<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Twig\Runtime;

use App\Service\GravatarService;
use Twig\Extension\RuntimeExtensionInterface;

class GravatarUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function getUrl(string $email, ?int $sizePixel = null): string
    {
        return GravatarService::getUrl($email, $sizePixel);
    }
}
