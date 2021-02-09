<?php

namespace App\Common\Controller;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints as Assert;

trait ValidatorTrait
{
    protected function violationsToArray(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        return $errors;
    }

    protected function validate(array $constraints, array $params): ConstraintViolationListInterface
    {
        $filteredConstraints = array_intersect_key($constraints, $params);
        return $this->validator->validate($params, new Assert\Collection($filteredConstraints));
    }
}
