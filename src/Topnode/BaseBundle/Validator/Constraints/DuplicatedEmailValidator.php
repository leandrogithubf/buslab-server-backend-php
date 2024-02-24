<?php

namespace App\Topnode\BaseBundle\Validator\Constraints;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DuplicatedEmailValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        if (0 === strlen($value)) {
            return;
        }

        $emails = $this->em->getRepository(User::class)->findByEmail($value);
        if (0 === count($emails)) {
            return;
        }

        foreach ($emails as $email) {
            if ($email->getIsLocked()) {
                $this->context->buildViolation($constraint->getMessageLocked())
                    ->setParameter('{{ string }}', $value)
                    ->addViolation()
                ;

                return false;
            }

            if ($email->getIsActive()) {
                $this->context->buildViolation($constraint->getMessage())
                    ->setParameter('{{ string }}', $value)
                    ->addViolation()
                ;

                return false;
            }
        }
    }
}
