<?php

namespace App\Topnode\BaseBundle\Validator\Constraints;

use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Brazanation\Documents\Cpf;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CpfValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (0 === strlen($value)) {
            return;
        }

        $cpf = Cpf::createFromString(StringUtils::onlyNumbers($value));
        if ($cpf instanceof Cpf) {
            return;
        }

        $this->context
            ->buildViolation($constraint->getMessage())
            ->setParameter('{{ string }}', $value)
            ->addViolation()
        ;
    }
}
