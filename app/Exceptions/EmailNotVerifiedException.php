<?php

namespace App\Exceptions;

use Exception;

class EmailNotVerifiedException extends Exception
{
    protected $message = 'Email not verified';
    protected $code = 403;
}
