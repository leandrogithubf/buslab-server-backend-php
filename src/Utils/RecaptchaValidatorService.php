<?php

namespace App\Utils;

class RecaptchaValidatorService
{
    public function validate($token, $action = '')
    {
        if (getenv('APP_ENV') === 'dev') {
            return true;
        }

        if (!getenv('RECAPTCHA_SECRETKEY')) {
            return true;
        }

        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode(getenv('RECAPTCHA_SECRETKEY')) . '&response=' . urlencode($token));
        $responseKeys = json_decode($response, true);

        if (isset($responseKeys['success']) && $responseKeys['success']) {
            return true;
        }

        return false;
    }
}
