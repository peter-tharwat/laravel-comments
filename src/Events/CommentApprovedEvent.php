<?php

namespace Spatie\Comments\Events;

use Spatie\Comments\Models\Comment;

class CommentApprovedEvent
{
    public function __construct(public Comment $comment)
    {
    }
}
