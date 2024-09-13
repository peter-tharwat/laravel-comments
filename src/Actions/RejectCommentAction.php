<?php

namespace Spatie\Comments\Actions;

use Spatie\Comments\Events\CommentRejectedEvent;
use Spatie\Comments\Models\Comment;

class RejectCommentAction
{
    public function execute(Comment $comment): Comment
    {
        $comment->delete();

        event(new CommentRejectedEvent($comment));

        return $comment;
    }
}
