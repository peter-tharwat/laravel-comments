<?php

namespace Spatie\Comments\Actions;

use Spatie\Comments\CommentTransformers\CommentTransformer;
use Spatie\Comments\Models\Comment;
use Spatie\Comments\Support\CommentSanitizer;
use Spatie\Comments\Support\Config;

class ProcessCommentAction
{
    public function execute(Comment $comment): void
    {
        Config::getCommentProcessors()->each(
            fn (CommentTransformer $commentProcessor) => $commentProcessor->handle($comment)
        );

        if (Config::getCommentProcessors()->isEmpty()) {
            $comment->text = $comment->original_text;
        }

        $comment->text = $this->sanitize($comment->text);
    }

    protected function sanitize(string|null $text = ''): string
    {
        $sanitizerClass = config('comments.comment_sanitizer') ?? CommentSanitizer::class;

        return app($sanitizerClass)->sanitize($text ?? '');
    }
}
