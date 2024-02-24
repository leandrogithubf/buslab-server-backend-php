<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCNH extends Constraint
{
    private $message = 'CNH já se encontra vinculada a uma empresa.';

    public function getMessage()
    {
        return $this->message;
    }

    public function validatedBy()
    {
        return UniqueCNHValidator::class;
    }
}
