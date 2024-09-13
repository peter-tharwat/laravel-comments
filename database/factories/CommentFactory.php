<?php

namespace Spatie\Comments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Comments\Models\Comment;
use Spatie\Comments\Tests\TestSupport\Models\BlogPost;
use Spatie\Comments\Tests\TestSupport\Models\Post;
use Spatie\Comments\Tests\TestSupport\Models\User;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'commentator_id' => User::factory(),
            'commentator_type' => User::class,
            'commentable_id' => Post::factory(),
            'commentable_type' => Post::class,
            'original_text' => 'original comment text',
            'text' => 'comment text',
        ];
    }
}
