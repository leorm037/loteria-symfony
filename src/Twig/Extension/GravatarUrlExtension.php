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

use App\Twig\Runtime\GravatarUrlExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GravatarUrlExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('gravatarUrl', [GravatarUrlExtensionRuntime::class, 'getUrl']),
        ];
    }

    /**
     * @return array<int,TwigFunction>
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('gravatarUrl', [GravatarUrlExtensionRuntime::class, 'getUrl']),
        ];
    }
}
