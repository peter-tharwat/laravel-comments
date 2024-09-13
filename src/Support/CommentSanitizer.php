<?php

namespace Spatie\Comments\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class CommentSanitizer
{
    public function sanitize(string $text): string
    {
        $config = (new HtmlSanitizerConfig())
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowSafeElements();

        foreach (config('comments.allowed_attributes') as $attribute => $allowedElements) {
            $config = $config->addAllowedElement($attribute, $allowedElements);
        }

        $sanitizer = new HtmlSanitizer($config);

        return $sanitizer->sanitize($text);
    }
}
