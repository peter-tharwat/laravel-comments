<?php

namespace Spatie\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Spatie\Comments\Models\Collections\ReactionCollection;
use Spatie\Comments\Models\Concerns\Interfaces\CanComment;
use Spatie\Comments\Support\Config;

/**
 * @property int $id
 * @property string $reaction
 * @property int $comment_id
 * @property int $commentator_id
 * @property int $commentator_type
 */
class Reaction extends Model
{
    public $guarded = [];

    public function commentator(): BelongsTo
    {
        return $this->morphTo();
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Config::getCommentModelName(), 'comment_id');
    }

    public function newCollection(array $models = []): ReactionCollection|Collection
    {
        return new ReactionCollection($models);
    }

    public function madeBy(CanComment $commentator): bool
    {
        if ($commentator->getMorphClass() !== $this->commentator->getMorphClass()) {
            return false;
        }

        return $commentator->getKey() === $this->commentator->getKey();
    }
}
