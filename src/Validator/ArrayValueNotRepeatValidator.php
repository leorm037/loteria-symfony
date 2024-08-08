<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ArrayValueNotRepeatValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var ArrayValueNotRepeat $constraint */
        if (!\is_array($value)) {
            return;
        }

        if (\count($value) !== \count(array_unique($value))) {
            $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', implode(', ', $value))
                    ->setCode('VAL001')
                    ->addViolation();
        }
    }
}
