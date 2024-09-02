<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\EventListener;

use App\Entity\Usuario;
use App\Helper\DateTimeHelper;
use App\Repository\UsuarioRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class LoginListener
{

    public function __construct(
            private UsuarioRepository $usuarioRepository
    )
    {
    }
    
    #[AsEventListener(event: InteractiveLoginEvent::class)]
    public function onLoginSuccessEvent(InteractiveLoginEvent $event): void
    {
        /** @var Usuario $usuario */
        $usuario = $event->getAuthenticationToken()->getUser();

        $request = $event->getRequest();
        $ip = $request->getClientIps();

        $usuario
                ->setIp($ip)
                ->setLastAccess(DateTimeHelper::currentDateTime())
        ;
        
        $this->usuarioRepository->save($usuario, true);
    }
}
