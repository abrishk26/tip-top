<?php

namespace App\Exceptions;

use Exception;

class UserInactiveException extends Exception
{
    protected $message = 'User account is inactive';
    protected $code = 403;
}
