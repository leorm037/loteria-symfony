<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioDadosType;
use App\Form\UsuarioSenhaType;
use App\Repository\UsuarioRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/usuario', name: 'app_usuario_')]
class UsuarioController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private UsuarioRepository $usuarioRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    #[Route('/dados', name: 'dados', methods: ['GET', 'POST'])]
    public function dados(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $form = $this->createForm(UsuarioDadosType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->usuarioRepository->save($usuario);

                $this->addFlash('success', 'Dados do usuário atualizados com sucesso.');

                return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $mensagem = \sprintf('O e-mail "%s" já está cadastrado.', $usuario->getEmail());

                $this->addFlash('danger', $mensagem);

                $this->logger->error($mensagem, $e->getTrace());

                return $this->render('usuario/dados.html.twig', [
                    'form' => $form,
                ]);
            }
        }

        return $this->render('usuario/dados.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/senha', name: 'senha', methods: ['GET', 'POST'])]
    public function senha(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $form = $this->createForm(UsuarioSenhaType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $usuario->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $usuario,
                    $form->get('plainPassword')->getData()
                )
            );

            $this->usuarioRepository->save($usuario);

            $this->addFlash('success', 'Senha alterada com sucesso!');

            return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/senha.html.twig', [
            'form' => $form,
        ]);
    }
}
