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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @phpstan-extends Voter<string, Bolao>
 */
class ApostaVoter extends Voter
{

    public const NEW = 'BOLAO_APOSTA_NEW';
    public const LIST = 'BOLAO_APOSTA_LIST';
    public const EDIT = 'BOLAO_APOSTA_EDIT';
    public const DELETE = 'BOLAO_APOSTA_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                    self::NEW,
                    self::LIST,
                    self::EDIT,
                    self::DELETE
                ]) && $subject instanceof Bolao;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof UserInterface) {
            return false;
        }

        $bolao = $subject;

        return match ($attribute) {
            self::NEW => $this->canNew($bolao, $usuario),
            self::LIST => $this->canList($bolao, $usuario),
            self::EDIT => $this->canEdit($bolao, $usuario),
            self::DELETE => $this->canDelete($bolao, $usuario),
            default => false
        };
    }

    private function canNew(Bolao $bolao, UserInterface $usuario): bool
    {
        return null === $bolao->getConcurso()->getDezenas() && $bolao->getUsuario() === $usuario;
    }

    private function canList(Bolao $bolao, UserInterface $usuario): bool
    {
        return $usuario === $bolao->getUsuario();
    }

    private function canEdit(Bolao $bolao, UserInterface $usuario): bool
    {
        return null === $bolao->getConcurso()->getDezenas() && $bolao->getUsuario() === $usuario;
    }

    private function canDelete(Bolao $bolao, UserInterface $usuario): bool
    {
        return null === $bolao->getConcurso()->getDezenas() && $bolao->getUsuario() === $usuario;
    }
}
