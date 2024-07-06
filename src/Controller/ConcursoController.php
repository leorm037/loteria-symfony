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

use App\Repository\ConcursoRepository;
use App\Repository\LoteriaRepository;
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
    )
    {
        
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $concursos = null;
        
        $loterias = $this->loteriaRepository->findAllOrderByNome();
        
        $loteriaUuid = $request->get('loteria');
        
        if($loteriaUuid) {
            $uuid = Uuid::fromString($loteriaUuid);
            $concursos = $this->concursoRepository->findByLoteriaUuid($uuid);
        }
        
        return $this->render('concurso/index.html.twig', [
                    'concursos' => $concursos,
                    'loterias' => $loterias
        ]);
    }
}
