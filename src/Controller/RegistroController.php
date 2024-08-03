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
use App\Form\UsuarioRegistroType;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/registrar', name: 'app_registro_')]
class RegistroController extends AbstractController
{

    public function __construct(
            private UserPasswordHasherInterface $userPasswordHasher,
            private UsuarioRepository $usuarioRepository
    )
    {
        
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $usuario = new Usuario();

        $form = $this->createForm(UsuarioRegistroType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $usuario->setPassword(
                    $this->userPasswordHasher->hashPassword(
                            $usuario,
                            $form->get('plainPassword')->getData()
                    )
            );

            $this->usuarioRepository->save($usuario, true);

            $this->addFlash("success", sprintf('Usuário "%s" cadastrado com sucesso.', $usuario->getEmail()));

            return $this->redirectToRoute('app_registro_index');
        }

        return $this->render('registro/index.html.twig', [
                    'form' => $form
        ]);
    }
}
