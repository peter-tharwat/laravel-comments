<?php

namespace Spatie\Comments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Spatie\Comments\Exceptions\CannotSendPendingCommentNotification;
use Spatie\Comments\Models\Concerns\HasComments;
use Spatie\Comments\Models\Concerns\Interfaces\CanComment;
use Spatie\Comments\Notifications\PendingCommentNotification;
use Spatie\Comments\Support\CommentatorProperties;
use Spatie\Comments\Support\Config;

/**
 * @property int $id
 * @property string $original_text
 * @property ?string $text
 * @property ?int $parent_id
 * @property int $commentable_id
 * @property string $commentable_type
 * @property int $commentator_id
 * @property string $commentator_type
 * @property ?Carbon $approved_at

 * @property array<mixed, mixed> $extra
 */
class Comment extends Model
{
    use HasComments;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'extra' => 'array',
        'approved_at' => 'datetime',
    ];

    public static function booted()
    {
        static::saving(function (Comment $comment) {
            $processCommentAction = Config::processCommentAction();

            $processCommentAction->execute($comment);
        });
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentator(): BelongsTo
    {
        return $this->morphTo();
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Config::getCommentModelName(), 'parent_id');
    }

    public function nestedComments(): HasMany
    {
        return $this->hasMany(Config::getCommentModelName(), 'parent_id');
    }

    public function topLevel(): Comment
    {
        if ($this->isTopLevel()) {
            return $this;
        }

        return $this->commentable->topLevel();
    }

    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    public function scopeTopLevel(Builder $builder): void
    {
        $builder->whereNull('parent_id');
    }

    public function scopePending(Builder $query): void
    {
        $query->whereNull('approved_at');
    }

    public function scopeApproved(Builder $query): void
    {
        $query->whereNotNull('approved_at');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Config::getReactionModelName());
    }

    public function findReaction(string $reaction, CanComment $commentator = null): ?Reaction
    {
        $commentator ??= auth()->user();

        if (! $commentator) {
            return null;
        }

        return $this->reactions()
            ->where('commentator_id', $commentator->getKey())
            ->where('commentator_type', $commentator->getMorphClass())
            ->where('reaction', $reaction)
            ->first();
    }

    public function unsortedReactionCounts(): HasMany
    {
        return $this->reactions()
            ->select('reaction', DB::raw('count(*) as count'))
            ->groupBy('reaction');
    }

    public function reactionCounts(): array
    {
        $allowedReactions = config('comments.allowed_reactions');

        return $this->unsortedReactionCounts
            ->sortBy(function (Reaction $reaction) use ($allowedReactions) {
                return array_search($reaction->reaction, $allowedReactions) ?? PHP_INT_MAX;
            })
            ->values()
            ->toArray();
    }

    public function react(string $reaction, CanComment $commentator = null): self
    {
        $commentator ??= auth()->user();

        $this->reactions()->firstOrCreate([
            'commentator_id' => $commentator?->getKey(),
            'commentator_type' => $commentator?->getMorphClass(),
            'reaction' => $reaction,
        ]);

        return $this;
    }

    public function deleteReaction(string $reaction, CanComment  $commentator = null): self
    {
        $commentator ??= auth()->user();

        $this
            ->reactions()
            ->where('commentator_id', $commentator->getKey())
            ->where('commentator_type', $commentator->getMorphClass())
            ->where('reaction', $reaction)
            ->delete();

        return $this;
    }

    /**
     * @return Collection<CanComment>
     */
    public function participatingCommentators(): Collection
    {
        return Comment::query()
            ->distinct('commentator_id', 'commentator_type')
            ->where('commentable_id', $this->commentable->getKey())
            ->where('commentable_type', $this->commentable->getMorphClass())
            ->approved()
            ->get()
            ->map(function (Comment $comment) {
                return [
                    'commentator_id' => $comment->commentator_id,
                    'commentator_type' => $comment->commentator_type,
                ];
            })
            ->filter(function (array $related) {
                return $related['commentator_type'] !== null;
            })
            ->filter(function (array $related) {
                if ($related['commentator_type'] !== $this->commentator_type) {
                    return true;
                }

                return $related['commentator_id'] !== $this->commentator_id;
            })
            ->groupBy('commentator_type')
            ->map(fn (Collection $related) => $related->pluck('commentator_id')->toArray())
            ->flatMap(
                fn (array $ids, string $class) =>
                    (str_contains($class, '\\') ? $class : Relation::morphMap()[$class])::query()
                        ->whereIn((new (str_contains($class, '\\')
                            ? $class
                            : Relation::morphMap()[$class])())->getKeyName(), $ids)->get()
            );
    }

    public function commentableName(): string
    {
        return $this->commentable->commentableName;
    }

    public function commentUrl(): string
    {
        return $this->topLevel()?->commentable?->commentUrl() . "#comment-{$this->id}";
    }

    public function commentatorProperties(): ?CommentatorProperties
    {
        /** @var CanComment $commentator */
        $commentator = $this->commentator;

        if (! $commentator) {
            return null;
        }

        return $commentator->commentatorProperties();
    }

    public function madeBy(?CanComment $commentator): bool
    {
        if (! $this->commentator) {
            return false;
        }

        if (! $commentator) {
            return false;
        }

        if ($commentator->getMorphClass() !== $this->commentator->getMorphClass()) {
            return false;
        }

        return $commentator->getKey() === $this->commentator->getKey();
    }

    public function wasMadeByDeletedCommentator(): bool
    {
        if ($this->wasMadeAnonymously()) {
            return false;
        }

        return $this->commentator === null;
    }

    public function wasMadeAnonymously(): bool
    {
        return $this->commentator_id === null;
    }

    public function approve(): self
    {
        Config::approveCommentAction()->execute($this);

        return $this;
    }

    public function reject(): self
    {
        Config::rejectCommentAction()->execute($this);

        return $this;
    }

    public function isPending(): bool
    {
        return ! $this->isApproved();
    }

    public function isApproved(): bool
    {
        return ! is_null($this->approved_at);
    }

    public function approveUrl(): string
    {
        return URL::signedRoute('comments::comment.approve', $this, now()->addWeek());
    }

    public function rejectUrl(): string
    {
        return URL::signedRoute('comments::comment.reject', $this, now()->addWeek());
    }

    public function shouldBeAutomaticallyApproved(): bool
    {
        if (Config::automaticallyApproveComments()) {
            return true;
        }

        if (! $currentUser = auth()->user()) {
            return false;
        }

        return $this->getApprovingUsers()->contains($currentUser);
    }

    public function getApprovingUsers(): Collection
    {
        $sendToClosure = PendingCommentNotification::$sendTo;

        if (! $sendToClosure) {
            return collect([]);
        }

        $users = once(fn () => $sendToClosure($this));

        if (is_iterable($users)) {
            $users = collect($users)
                ->each(function (object $user) {
                    if (! self::implementsNotifiable($user)) {
                        throw CannotSendPendingCommentNotification::doesNotImplementNotifiable();
                    }
                });

            return $users;
        }

        if (is_object($users)) {
            if (! self::implementsNotifiable($users)) {
                throw CannotSendPendingCommentNotification::doesNotImplementNotifiable();
            }

            return collect([$users]);
        }

        throw CannotSendPendingCommentNotification::doesNotImplementNotifiable();
    }

    protected static function implementsNotifiable(object $object): bool
    {
        $traitsUsed = trait_uses_recursive($object);

        return in_array(Notifiable::class, $traitsUsed);
    }
}
