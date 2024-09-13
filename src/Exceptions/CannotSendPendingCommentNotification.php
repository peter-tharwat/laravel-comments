<?php

namespace Spatie\Comments\Exceptions;

use Exception;

class CannotSendPendingCommentNotification extends Exception
{
    public static function doesNotImplementNotifiable(): self
    {
        return new self("The items in the array/Collection returned by sendTo should all use the Notifiable trait");
    }
}
