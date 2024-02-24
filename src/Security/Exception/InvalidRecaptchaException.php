<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidRecaptchaException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'INVALID_RECAPTCHA';
    }
}
