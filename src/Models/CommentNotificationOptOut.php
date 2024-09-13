<?php

namespace Spatie\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Comments\Support\Config;

/**
 * @property int $commentator_id
 * @property string $commentator_type
 */
class CommentNotificationOptOut extends Model
{
    public $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::getCommentatorModelName(), 'user_id');
    }

    public function commentator(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
