<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueDocument extends Constraint
{
    private $message = 'Documento de indentificação já cadastrado.';

    public function getMessage()
    {
        return $this->message;
    }

    public function validatedBy()
    {
        return UniqueDocumentValidator::class;
    }
}
