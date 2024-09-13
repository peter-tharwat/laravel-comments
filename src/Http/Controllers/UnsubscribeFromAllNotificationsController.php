<?php

namespace Spatie\Comments\Http\Controllers;

class UnsubscribeFromAllNotificationsController
{
    public function askConfirmation()
    {
        return view('comments::signed.notificationSubscription.unsubscribeAllApproval');
    }

    public function unsubscribeAll(string $userClass, string $userId)
    {
        /** @var \Spatie\Comments\Models\Concerns\Interfaces\CanComment $user */
        $user = $userClass::find($userId);

        $user->unsubscribeFromAllCommentNotifications();

        return view('comments::signed.notificationSubscription.unsubscribe');
    }
}
