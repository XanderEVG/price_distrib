<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsMacValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $match_str = "/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/i";
        $count = preg_match_all($match_str, $value, $matches);

        if ($count == 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}