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

use App\Entity\Apostador;
use App\Entity\Bolao;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ApostadorVoter extends Voter
{

    public const NEW = 'BOLAO_APOSTADOR_NEW';
    public const LIST = 'BOLAO_APOSTADOR_LIST';
    public const EDIT = 'BOLAO_APOSTADOR_EDIT';
    public const DELETE = 'BOLAO_APOSTADOR_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                    self::NEW,
                    self::LIST,
                    self::EDIT,
                    self::DELETE
                ]) && ($subject instanceof Apostador || $subject instanceof Bolao);
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
                self::NEW => $this->canNew($bolao, $usuario),
                self::LIST => $this->canList($bolao, $usuario),
                default => false
            };
        }

        if ($subject instanceof Apostador) {
            $apostador = $subject;
            
            return match ($attribute) {
                self::EDIT => $this->canEdit($apostador, $usuario),
                self::DELETE => $this->canDelete($apostador, $usuario),
                default => false
            };
        }

        return false;
    }

    private function canNew(Bolao $bolao, Usuario $usuario): bool
    {
        return $usuario === $bolao->getUsuario() && null === $bolao->getConcurso()->getDezenas();
    }

    private function canList(Bolao $bolao, Usuario $usuario): bool
    {
        return $usuario === $bolao->getUsuario();
    }

    private function canEdit(Apostador $apostador, Usuario $usuario): bool
    {
        return $usuario === $apostador->getBolaoUsuario() && null === $apostador->getBolaoConcurso()->getDezenas();
    }

    private function canDelete(Apostador $apostador, Usuario $usuario): bool
    {
        return $usuario === $apostador->getBolaoUsuario() && null === $apostador->getBolaoConcurso()->getDezenas();
    }
}
