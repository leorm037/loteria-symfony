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

use App\Entity\Loteria;
use App\Form\LoteriaType;
use App\Repository\LoteriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/loteria', name: 'app_loteria_')]
class LoteriaController extends AbstractController
{

    public function __construct(
            private LoteriaRepository $loteriaRepository
    )
    {
        
    }

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $registrosPorPaginas = $request->get('registros-por-pagina', 10);

        $pagina = $request->get('pagina', 1);

        $loterias = $this->loteriaRepository->list($registrosPorPaginas, $pagina);

        return $this->render('loteria/index.html.twig', [
                    'loterias' => $loterias
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $loteria = new Loteria();

        $form = $this->createForm(LoteriaType::class, $loteria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->loteriaRepository->save($loteria, true);
            
            $this->addFlash('success', sprintf('Loteria "%s" cadastrada com sucesso!', $loteria->getNome()));
            
            return $this->redirectToRoute('app_loteria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('loteria/new.html.twig', [
                    'form' => $form
        ]);
    }

    #[Route('/{uuid:loteria}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request, Loteria $loteria): Response
    {
        $form = $this->createForm(LoteriaType::class, $loteria);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->loteriaRepository->save($loteria, true);
            
            $this->addFlash('success', sprintf('Loteria "%s" alterada com sucesso.', $loteria->getNome()));

            return $this->redirectToRoute('app_loteria_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('loteria/edit.html.twig', [
            'form' => $form
        ]);
    }
}
