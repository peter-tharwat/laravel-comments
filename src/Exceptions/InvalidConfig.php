<?php

namespace Spatie\Comments\Exceptions;

use Exception;

class InvalidConfig extends Exception
{
    public static function couldNotDetermineCommentatorModelName(): self
    {
        return new self("Could not determine the commentator model name. Make sure you specified a valid commentator model in the comments config file");
    }
}
