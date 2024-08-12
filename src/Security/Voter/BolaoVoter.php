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

class BolaoVoter extends Voter
{

    public const EDIT = 'BOLAO_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return \in_array($attribute, [self::EDIT]) && $subject instanceof Bolao;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof UserInterface) {
            return false;
        }

        $bolao = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($bolao, $usuario),
            default => false
        };
    }

    private function canEdit(Bolao $bolao, Usuario $usuario): bool
    {
        return $usuario === $bolao->getUsuario();
    }
}
