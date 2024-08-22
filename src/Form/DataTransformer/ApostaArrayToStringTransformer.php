<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Description of ApostaArrayToStringTransformer
 *
 * @author leona
 */
class ApostaArrayToStringTransformer implements DataTransformerInterface
{

    public function transform($dezenasAsArray): string
    {
        return implode(',', $dezenasAsArray);
    }

    private function lpad(int $value): string
    {
        
    }

    public function reverseTransform($dezenasAsString): array
    {
        $dezenasArray = explode(',', $dezenasAsString);

        $func = function (int $value): string {
            return str_pad($value, 2, '00', STR_PAD_LEFT);
        };

        return array_map($func, $dezenasArray);
    }
}
