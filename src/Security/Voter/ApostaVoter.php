<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Security\Voter;

use App\Entity\Bolao;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ApostaVoter extends Voter
{

    public const LIST = 'BOLAO_APOSTA_LIST';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                    self::LIST
                ]) && $subject instanceof Bolao;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof Bolao) {
            $bolao = $subject;

            return match ($attribute) {
                self::LIST => $this->canList($bolao, $usuario),
                default => false
            };
        }

        return false;
    }

    private function canList(Bolao $bolao, Usuario $usuario): bool
    {
        return $usuario === $bolao->getUsuario();
    }
}
