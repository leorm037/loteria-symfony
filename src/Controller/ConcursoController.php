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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/concurso', name: 'app_concurso_', methods: ['GET'])]
class ConcursoController extends AbstractController
{

    public function __construct(
            private ConcursoRepository $concursoRepository,
            private LoteriaRepository $loteriaRepository,
            private KernelInterface $kernel
    )
    {
        
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

    #[Route('/conferir', name: 'conferir')]
    public function conferir(): Response
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $concursoRecuperarResultado = new ArrayInput([
            'command' => 'loteria:concurso:recuperar-resultado'
        ]);
        $apostaConferir = new ArrayInput([
            'command' => 'loteria:aposta:conferir'
        ]);

        $output = new BufferedOutput();

        $application->run($concursoRecuperarResultado, $output);

        $content = nl2br($output->fetch());

        $this->addFlash('success', $content);

        $application->run($apostaConferir, $output);

        $content = nl2br($output->fetch());

        $this->addFlash('success', $content);

        return $this->redirectToRoute('app_concurso_index');
    }
}
