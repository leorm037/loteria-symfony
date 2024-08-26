<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<array, string>
 */
class ApostaArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * @param array<int,string> $dezenasAsArray
     */
    public function transform($dezenasAsArray): string
    {
        return implode(',', $dezenasAsArray);
    }

    /**
     * @return array<int,string>
     */
    public function reverseTransform($dezenasAsString): array
    {
        $dezenasArray = explode(',', $dezenasAsString);

        $func = function (string $value): string {
            return str_pad($value, 2, '00', \STR_PAD_LEFT);
        };

        return array_map($func, $dezenasArray);
    }
}
