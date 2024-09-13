<?php

namespace Spatie\Comments\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Comments\Actions\ApproveCommentAction;
use Spatie\Comments\Actions\ProcessCommentAction;
use Spatie\Comments\Actions\RejectCommentAction;
use Spatie\Comments\Actions\SendNotificationsForApprovedCommentAction;
use Spatie\Comments\Actions\SendNotificationsForPendingCommentAction;
use Spatie\Comments\CommentTransformers\CommentTransformer;
use Spatie\Comments\Exceptions\InvalidConfig;
use Spatie\Comments\Models\Comment;
use Spatie\Comments\Models\CommentNotificationSubscription;
use Spatie\Comments\Models\Concerns\Interfaces\CanComment;
use Spatie\Comments\Models\Reaction;

class Config
{
    public static function automaticallyApproveComments(): bool
    {
        return config('comments.automatically_approve_all_comments');
    }

    /** @return class-string<\Spatie\Comments\Models\Concerns\Interfaces\CanComment> */
    public static function getCommentatorModelName(): string
    {
        return config('comments.models.commentator')
            ?? throw InvalidConfig::couldNotDetermineCommentatorModelName();
    }

    /** @return class-string<\Spatie\Comments\Models\Concerns\Interfaces\CanComment> */
    public static function getCommentatorModelNameField(): string
    {
        return config('comments.models.name', 'name');
    }

    /** @return class-string<Comment> */
    public static function getCommentModelName(): string
    {
        return config('comments.models.comment');
    }

    /** @return class-string<CommentNotificationSubscription> */
    public static function getCommentNotificationSubscriptionModelName(): string
    {
        return config('comments.models.comment_notification_subscription');
    }

    public static function getGravatarDefaultImage(): string
    {
        return config('comments.gravatar.default_image', 'mp');
    }

    /** @return class-string<Reaction> */
    public static function getReactionModelName(): string
    {
        return config('comments.models.reaction');
    }

    public static function processCommentAction(): ProcessCommentAction
    {
        $actionClass = config('comments.actions.process_comment');

        return app($actionClass);
    }

    public static function approveCommentAction(): ApproveCommentAction
    {
        $actionClass = config('comments.actions.approve_comment');

        return app($actionClass);
    }

    public static function rejectCommentAction(): RejectCommentAction
    {
        $actionClass = config('comments.actions.reject_comment');

        return app($actionClass);
    }

    public static function sendNotificationsForPendingCommentAction(): SendNotificationsForPendingCommentAction
    {
        $actionClass = config('comments.actions.send_notifications_for_pending_comment');

        return app($actionClass);
    }

    public static function sendNotificationsForApprovedCommentAction(): SendNotificationsForApprovedCommentAction
    {
        $actionClass = config('comments.actions.send_notifications_for_approved_comment');

        return app($actionClass);
    }

    /** @return Collection<CommentTransformer> */
    public static function getCommentProcessors(): Collection
    {
        return collect(config('comments.comment_transformers'))
            ->map(fn (string $commentProcessorClass) => app($commentProcessorClass));
    }

    public static function getPendingCommentNotification(Comment $comment): Notification
    {
        $notificationClass = config('comments.notifications.notifications.pending_comment');

        return app($notificationClass, ['comment' => $comment]);
    }

    public static function getApprovedCommentNotification(Comment $comment, CanComment $commentator): Notification
    {
        $notificationClass = config('comments.notifications.notifications.approved_comment');

        return app($notificationClass, compact('comment', 'commentator'));
    }
}
