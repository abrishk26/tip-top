<?php

namespace App\Exceptions;

use Exception;

class InvalidVerificationTokenException extends Exception
{
    protected $message = 'Invalid or expired verification token';
    protected $code = 400;
}
