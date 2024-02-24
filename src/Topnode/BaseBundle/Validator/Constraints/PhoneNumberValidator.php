<?php

namespace App\Topnode\BaseBundle\Validator\Constraints;

use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates an Brazilian phone number (mobile or not).
 */
class PhoneNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $value = StringUtils::onlyNumbers($value ?? '');

        // An empty number is valid as we can't say if it is or not required
        if (strlen($value) === 0) {
            return;
        }

        // Validates if the number has 10 or 11 chars and if the first number
        // equals to 0, as no DDD can be bellow 10 (we do not test for currently
        // non used DDDs as 10, 20, 30, 52, 53...lets avoid any strange bugs)
        if (!preg_match('/^\d{10,11}$/', $value) || substr($value, 0, 1) == '0') {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;

            return;
        }

        $number = substr($value, 2); // Number without the DDD (first two digits)

        // Checks if the number has only the same digit
        if (preg_match('/^(\d)\1+$/', $number)) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;

            return;
        }

        // Invalid starter
        if (in_array(substr($number, 0, 1), ['0', '1', '6', '8'])) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;

            return;
        }

        // Valid
        return;
    }
}
