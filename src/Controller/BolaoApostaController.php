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

use App\Repository\ApostaRepository;
use App\Repository\BolaoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use App\Security\Voter\ApostaVoter;

#[Route('/bolao/{uuid}/apostas', name: 'app_bolao_apostas_', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
class BolaoApostaController extends AbstractController
{

    public function __construct(
            private ApostaRepository $apostaRepository,
            private BolaoRepository $bolaoRepository
    )
    {
        
    }

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $apostas = $this->apostaRepository->findApostasByUuidBolao($bolao->getUuid());

        $this->denyAccessUnlessGranted(ApostaVoter::LIST, $bolao);

        return $this->render('bolao_aposta/index.html.twig', [
                    'apostas' => $apostas,
                    'bolao' => $bolao,
        ]);
    }
}
