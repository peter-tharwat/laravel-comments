<?php

namespace Spatie\Comments\Http\Controllers;

use Spatie\Comments\Models\Comment;
use Spatie\Comments\Support\Config;

class RejectCommentController
{
    public function askConfirmation(Comment $comment)
    {
        return view('comments::signed.approval.rejectCommentApproval', compact('comment'));
    }

    public function reject(Comment $comment)
    {
        Config::rejectCommentAction()->execute($comment);

        return view('comments::signed.approval.rejectComment', compact('comment'));
    }
}
