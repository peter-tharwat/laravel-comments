<?php

namespace Spatie\Comments\Models\Concerns\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Comments\Enums\NotificationSubscriptionType;
use Spatie\Comments\Support\CommentatorProperties;

interface CanComment
{
    public function commentatorComments(): MorphMany;

    public function reactions(): MorphMany;

    public function commentatorProperties(): CommentatorProperties;

    public function getKey();

    public function getMorphClass();

    public function notify($instance);

    public function subscribeToCommentNotifications(Model $hasComments, NotificationSubscriptionType $subscriptionType);

    public function unsubscribeFromCommentNotifications(Model $hasComments): self;

    public function unsubscribeFromAllCommentNotifications(): self;

    public function notificationSubscriptionType(Model $hasComment): ?NotificationSubscriptionType;
}
