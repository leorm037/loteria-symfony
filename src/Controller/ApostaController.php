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

use App\DTO\ApostaImportarDTO;
use App\Form\ApostaImportarType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/aposta', name: 'app_aposta_')]
class ApostaController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('aposta/index.html.twig', [
            'controller_name' => 'ApostaController',
        ]);
    }

    #[Route('/importar', name: 'importar')]
    public function importar(Request $request): Response
    {
        $apostaImportarDTO = new ApostaImportarDTO();

        $form = $this->createForm(ApostaImportarType::class, $apostaImportarDTO);
        $form->handleRequest($request);

        return $this->render('aposta/importar.html.twig', [
            'form' => $form,
            'apostaImportar' => $apostaImportarDTO,
        ]);
    }
}
