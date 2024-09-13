<?php

namespace Spatie\Comments\Exceptions;

use Exception;

class CannotCreateComment extends Exception
{
    public static function userIsRequired(): self
    {
        return new self("Creating a comment without a user is not allowed");
    }
}
