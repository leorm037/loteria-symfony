<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Twig\Extension;

use App\Twig\Runtime\ContarApostasExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ContarApostasExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('contarApostas', [ContarApostasExtensionRuntime::class, 'contarApostas']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('contarApostas', [ContarApostasExtensionRuntime::class, 'contarApostas']),
        ];
    }
}
