<?php

namespace Spatie\Comments\Notifications;

use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Comments\Models\Comment;

class PendingCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public static ?Closure $sendTo = null;

    public function __construct(public Comment $comment)
    {
    }

    public function via()
    {
        return 'mail';
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->from(config('comments.notifications.mail.from.address', config('mail.from.address')), config('comments.notifications.mail.from.name', config('mail.from.name')))
            ->subject(trans('comments::notifications.pending_comment_mail_subject'))
            ->markdown('comments::mail.pendingCommentNotification', [
                'comment' => $this->comment,
                'topLevelComment' => $this->comment->topLevel(),
            ]);
    }

    public static function sendTo(Closure $sendTo)
    {
        static::$sendTo = $sendTo;
    }
}
