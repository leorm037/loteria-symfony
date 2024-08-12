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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BolaoApostaVoter extends Voter
{

    public const NEW = 'BOLAO_APOSTA_NEW';
    public const VIEW = 'BOLAO_APOSTA_VIEW';
    public const EDIT = 'BOLAO_APOSTA_EDIT';
    public const DELETE = 'BOLAO_APOSTA_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::NEW, self::EDIT, self::VIEW, self::DELETE]) && $subject instanceof \App\Entity\BolaoAposta;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::NEW:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::DELETE:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;
        }

        return false;
    }
}
