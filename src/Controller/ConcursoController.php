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
use App\Repository\ConcursoRepository;
use App\Repository\LoteriaRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/concurso', name: 'app_concurso_', methods: ['GET'])]
class ConcursoController extends AbstractController
{
    public function __construct(
        private ConcursoRepository $concursoRepository,
        private LoteriaRepository $loteriaRepository
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $concursos = null;

        $loteria = null;

        $loterias = $this->loteriaRepository->findAllOrderByNome();

        return $this->render('concurso/index.html.twig', [
            'concursos' => $concursos,
            'loterias' => $loterias,
            'loteria' => $loteria,
        ]);
    }

    #[Route('/loteria/{uuid}/', name: 'loteria')]
    public function loteria(#[MapEntity(expr: 'repository.findByUuid(uuid)')] Loteria $loteria): Response
    {
        $concursos = null;

        $loterias = $this->loteriaRepository->findAllOrderByNome();

        $concursos = $this->concursoRepository->findByLoteria($loteria);

        return $this->render('concurso/index.html.twig', [
            'concursos' => $concursos,
            'loterias' => $loterias,
            'loteria' => $loteria,
        ]);
    }

    #[Route('/{uuid}/recuperar', name: 'recuperar', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function recuperarResultado(Request $request): Response
    {
        $loteria = null;

        $concursoUuid = $request->get('uuid');

        if ($concursoUuid) {
            $uuid = Uuid::fromString($concursoUuid);
        }

        return $this->redirectToRoute('app_concurso_index', ['loteria' => $loteria]);
    }
}
