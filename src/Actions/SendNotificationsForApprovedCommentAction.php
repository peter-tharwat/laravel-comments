<?php

namespace Spatie\Comments\Actions;

use Spatie\Comments\Jobs\SendNotificationsForApprovedCommentJob;
use Spatie\Comments\Models\Comment;

class SendNotificationsForApprovedCommentAction
{
    public function execute(Comment $comment): void
    {
        dispatch(new SendNotificationsForApprovedCommentJob($comment));
    }
}
