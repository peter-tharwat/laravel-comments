<?php

namespace Spatie\Comments\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Comments\Models\Comment;
use Spatie\Comments\Support\Config;

class SendNotificationsForPendingCommentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Comment $comment)
    {
    }

    public function handle()
    {
        if (! $this->comment->isPending()) {
            return;
        }

        $notification = Config::getPendingCommentNotification($this->comment);

        $this->comment
            ->getApprovingUsers()
            ->each(fn (object $user) => $user->notify($notification));
    }
}
