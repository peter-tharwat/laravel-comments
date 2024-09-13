<?php

namespace Spatie\Comments\Actions;

use Spatie\Comments\Jobs\SendNotificationsForPendingCommentJob;
use Spatie\Comments\Models\Comment;

class SendNotificationsForPendingCommentAction
{
    public function execute(Comment $comment)
    {
        dispatch(new SendNotificationsForPendingCommentJob($comment));
    }
}
