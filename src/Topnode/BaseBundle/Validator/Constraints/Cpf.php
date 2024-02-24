<?php

namespace App\Topnode\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Cpf extends Constraint
{
    private $message = 'CPF inválido.';

    public function getMessage()
    {
        return $this->message;
    }
}
