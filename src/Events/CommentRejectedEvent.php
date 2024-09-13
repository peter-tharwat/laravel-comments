<?php

namespace Spatie\Comments\Events;

use Spatie\Comments\Models\Comment;

class CommentRejectedEvent
{
    public function __construct(public Comment $comment)
    {
    }
}
