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

use App\Twig\Runtime\GravatarExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GravatarExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('gravatar', [GravatarExtensionRuntime::class, 'getUrl']),
        ];
    }

}
