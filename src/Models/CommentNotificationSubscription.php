<?php

namespace Spatie\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Comments\Enums\NotificationSubscriptionType;

class CommentNotificationSubscription extends Model
{
    public $guarded = [];

    public $casts = [
        'type' => NotificationSubscriptionType::class,
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }
}
