<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCode extends Constraint
{
    private $message = 'Código já cadastrado.';

    public function getMessage()
    {
        return $this->message;
    }

    public function validatedBy()
    {
        return UniqueCodeValidator::class;
    }
}
