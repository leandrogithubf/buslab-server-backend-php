<?php

namespace App\Topnode\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PhoneNumber extends Constraint
{
    private $message = 'Número inválido.';

    public function getMessage()
    {
        return $this->message;
    }
}
