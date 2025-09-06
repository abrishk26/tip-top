<?php

namespace App\Exceptions;

use Exception;

class UnverifiedUserException extends Exception
{
    public ?bool $is_verified;
    public ?string $registration_status;

    public function __construct(
        string $message = "User is not verified",
        ?bool $is_verified =  null,
        ?string $registration_status = null,
    ) {
        parent::__construct($message);
        $this->is_verified = $is_verified;
        $this->registration_status = $registration_status;
    }
}
