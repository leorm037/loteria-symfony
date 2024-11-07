<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\MessageHandler;

use App\Message\BolaoResultadoNotificarMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class BolaoResultadoNotificarMessageHandler
{
    public function __invoke(BolaoResultadoNotificarMessage $message): void
    {
        // do something with your message
    }
}
