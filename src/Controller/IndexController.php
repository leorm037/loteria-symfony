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

use App\Entity\Bolao;
use App\Repository\ApostaRepository;
use App\Repository\BolaoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_')]
class IndexController extends AbstractController
{
    public function __construct(
        private BolaoRepository $bolaoRepository,
        private ApostaRepository $apostaRepository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/test', name: 'test')]
    public function test(): Response
    {
        /** @var Bolao $bolao */
        $bolao = $this->bolaoRepository->findOneBy(['nome' => 'Teste 1']);
        $apostas = $this->apostaRepository->findApostasByUuidBolao($bolao->getUuid());

        return $this->render('email/bolao/notificarResultadoBolao.html.twig', [
            'bolao' => $bolao,
            'apostas' => $apostas,
        ]);
    }
}
