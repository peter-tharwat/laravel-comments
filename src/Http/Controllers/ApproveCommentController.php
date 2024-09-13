<?php

namespace Spatie\Comments\Http\Controllers;

use Spatie\Comments\Models\Comment;
use Spatie\Comments\Support\Config;

class ApproveCommentController
{
    public function askConfirmation(Comment $comment)
    {
        return view('comments::signed.approval.approveCommentConfirmation', compact('comment'));
    }

    public function approve(Comment $comment)
    {
        Config::approveCommentAction()->execute($comment);

        return view('comments::signed.approval.approveComment', compact('comment'));
    }
}
