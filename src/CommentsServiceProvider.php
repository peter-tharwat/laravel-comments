<?php

namespace Spatie\Comments;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Spatie\Comments\Http\Controllers\ApproveCommentController;
use Spatie\Comments\Http\Controllers\RejectCommentController;
use Spatie\Comments\Http\Controllers\UnsubscribeFromAllNotificationsController;
use Spatie\Comments\Http\Controllers\UnsubscribeFromNotificationsController;
use Spatie\Comments\Support\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CommentsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-comments')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasMigration('create_comments_tables');
    }

    public function packageBooted()
    {
        $this
            ->registerRoutes()
            ->registerBladeComponents();
    }

    protected function registerRoutes(): self
    {
        Route::model('laravelCommentsComment', Config::getCommentModelName());

        Route::macro('comments', function (string $prefix = '') {
            Route::prefix($prefix)->middleware(['web', 'signed'])->group(function () use ($prefix) {
                Route::get("comment-approval/approve/{laravelCommentsComment}", [ApproveCommentController::class, 'askConfirmation'])->name('comments::comment.approve');
                Route::post("comment-approval/approve/{laravelCommentsComment}", [ApproveCommentController::class, 'approve']);

                Route::get("comment-approval/reject/{laravelCommentsComment}", [RejectCommentController::class, 'askConfirmation'])->name('comments::comment.reject');
                Route::post("comment-approval/reject/{laravelCommentsComment}", [RejectCommentController::class, 'reject']);

                Route::get('comment-subscription/unsubscribe', [UnsubscribeFromNotificationsController::class, 'askConfirmation'])->name('comments::notifications.unsubscribe');
                Route::post('comment-subscription/unsubscribe', [UnsubscribeFromNotificationsController::class, 'unsubscribe']);

                Route::get('comment-subscription/unsubscribe-all', [UnsubscribeFromAllNotificationsController::class, 'askConfirmation'])->name('comments::unsubscribe-all');
                Route::post('comment-subscription/unsubscribe-all', [UnsubscribeFromAllNotificationsController::class, 'unsubscribeAll']);
            });
        });

        return $this;
    }

    public function registerBladeComponents(): self
    {
        Blade::component('comments::signed.signedLayout', 'comments::signed-layout');

        return $this;
    }
}
