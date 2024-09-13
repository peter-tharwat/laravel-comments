<?php

namespace Spatie\Comments\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Spatie\Comments\Enums\NotificationSubscriptionType;
use Spatie\Comments\Exceptions\CannotCreateComment;
use Spatie\Comments\Models\Comment;
use Spatie\Comments\Models\CommentNotificationSubscription;
use Spatie\Comments\Models\Concerns\Interfaces\CanComment;
use Spatie\Comments\Support\Config;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Config::getCommentModelName(), 'commentable');
    }

    public function notificationSubscriptions(): MorphMany
    {
        return $this->morphMany(Config::getCommentNotificationSubscriptionModelName(), 'commentable');
    }

    public function subscribers(NotificationSubscriptionType $type = null): Collection
    {
        return $this
            ->notificationSubscriptions()
            ->when($type, fn (Builder $builder) => $builder->where('type', $type))
            ->with('subscriber')
            ->get()
            ->map(fn (CommentNotificationSubscription $subscription) => $subscription->subscriber)
            ->filter();
    }

    public function comment(string $text, CanComment $commentator = null): Comment
    {
        $commentator ??= auth()->user();

        if (! config('comments.allow_anonymous_comments')) {
            if (! $commentator) {
                throw CannotCreateComment::userIsRequired();
            }
        }

        $parentId = ($this::class === Config::getCommentModelName())
            ? $this->getKey()
            : null;

        $comment = $this->comments()->create([
            'commentator_id' => $commentator?->getKey() ?? null,
            'commentator_type' => $commentator?->getMorphClass() ?? null,
            'original_text' => $text,
            'parent_id' => $parentId,
        ]);

        if ($comment->shouldBeAutomaticallyApproved()) {
            Config::approveCommentAction()->execute($comment);

            return $comment;
        }

        if (config('comments.notifications.enabled')) {
            Config::sendNotificationsForPendingCommentAction()->execute($comment);
        }

        return $comment;
    }

    /**
     * @return Collection<CanComment>
     */
    public function participatingCommentators(): Collection
    {
        return $this->comments()
            ->distinct('commentator_id', 'commentator_type')
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
            ->groupBy('commentator_type')
            ->map(fn (Collection $related) => $related->pluck('commentator_id')->toArray())
            ->flatMap(function (array $ids, string $class) {
                if (! class_exists($class)) {
                    $class = Relation::getMorphedModel($class);
                }

                return $class::query()->whereIn((new $class())->getKeyName(), $ids)->get();
            });
    }

    /*
     * This string will be used in notifications on what a new comment
     * was made.
     */
    abstract public function commentableName(): string;

    /*
     * This URL will be used in notifications to let the user know
     * where the comment itself can be read.
     */
    abstract public function commentUrl(): string;
}
