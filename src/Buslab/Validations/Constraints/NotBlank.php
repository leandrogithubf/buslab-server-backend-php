<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotBlank extends Constraint
{
    private $message = 'Campos obrigatórios não preenchidos.';

    public function getMessage()
    {
        return $this->message;
    }

    public function validatedBy()
    {
        return NotBlankValidator::class;
    }
}
