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

use App\Entity\Aposta;
use App\Entity\Bolao;
use App\Form\ApostaType;
use App\Repository\ApostaRepository;
use App\Repository\BolaoRepository;
use App\Security\Voter\ApostaVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use function dd;

#[Route('/bolao', name: 'app_bolao_apostas_')]
class BolaoApostaController extends AbstractController
{

    public function __construct(
            private ApostaRepository $apostaRepository,
            private BolaoRepository $bolaoRepository
    )
    {
        
    }

    #[Route('/{uuid}/apostas', name: 'index', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
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

    #[Route('/{uuid:bolao}/apostas/new', name: 'new', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function new(Request $request, Bolao $bolao): Response
    {
        $aposta = new Aposta();
        $aposta->setBolao($bolao);

        $form = $this->createForm(ApostaType::class, $aposta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->apostaRepository->save($aposta, true);
            
            $this->addFlash('success', sprintf('Dezenas "%s" cadastradas com sucesso.', implode(', ', $aposta->getDezenas())));
            
            return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('bolao_aposta/new.html.twig', [
                    'form' => $form,
                    'bolao' => $bolao
        ]);
    }

    #[Route('/apostas/{uuid:aposta}/edit', name: 'edit', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Aposta $aposta): Response
    {
        $bolao = $aposta->getBolao();
        
        $form = $this->createForm(ApostaType::class, $aposta);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->apostaRepository->save($aposta, true);
            
            $this->addFlash('success', sprintf('Dezenas "%s" alteradas com sucesso.', implode(', ', $aposta->getDezenas())));
            
            return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);            
        }
        
        return $this->render('bolao_aposta/edit.html.twig', [
                    'form' => $form,
                    'bolao' => $bolao
        ]);
    }

    #[Route('/apostas/delete', name: 'delete', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['POST'])]
    public function delete(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $aposta = $this->apostaRepository->findByUuid($uuid);

        dd($aposta);
    }
}
