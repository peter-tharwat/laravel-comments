<?php

namespace Spatie\Comments\CommentTransformers;

use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use Spatie\Comments\Models\Comment;
use Spatie\LaravelMarkdown\MarkdownRenderer;

class MarkdownToHtmlTransformer implements CommentTransformer
{
    public function __construct(protected MarkdownRenderer $markdownRenderer)
    {
    }

    public function handle(Comment $comment): void
    {
        $this->markdownRenderer->addExtension(new DisallowedRawHtmlExtension());

        $comment->text = $this->markdownRenderer->toHtml($comment->original_text);
    }
}
