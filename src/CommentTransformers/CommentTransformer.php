<?php

namespace Spatie\Comments\CommentTransformers;

use Spatie\Comments\Models\Comment;

interface CommentTransformer
{
    public function handle(Comment $comment): void;
}
