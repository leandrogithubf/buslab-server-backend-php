<?php

namespace App\Topnode\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DuplicatedEmail extends Constraint
{
    private $message = 'Este e-mail já está sendo usado.';
    private $messageLocked = 'Uma conta para esse e-mail já existe e se encontra desativada. Se possuir alguma dúvida, entre em contato conosco para mais informações.';

    public function getMessage()
    {
        return $this->message;
    }

    public function getMessageLocked()
    {
        return $this->messageLocked;
    }
}
