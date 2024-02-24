<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniquePrefix extends Constraint
{
    private $message = 'Este prefixo já está em uso em sua cidade.';

    public function getMessage()
    {
        return $this->message;
    }

    public function validatedBy()
    {
        return UniquePrefixValidator::class;
    }
}
