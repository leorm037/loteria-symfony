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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/concurso', name: 'app_concurso_', methods: ['GET'])]
class ConcursoController extends AbstractController
{
    public function __construct(
        private ConcursoRepository $concursoRepository,
        private LoteriaRepository $loteriaRepository,
        private KernelInterface $kernel,
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

    #[Route('/loteria/{uuid:loteria}/', name: 'loteria', methods: ['GET'], requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function loteria(Request $request, Loteria $loteria): Response
    {
        $registrosPorPaginas = $request->get('registros-por-pagina', 10);
        $pagina = $request->get('pagina', 1);

        $loterias = $this->loteriaRepository->findAllOrderByNome();

        $concursos = $this->concursoRepository->findByLoteria($loteria, $registrosPorPaginas, $pagina);

        return $this->render('concurso/index.html.twig', [
            'concursos' => $concursos,
            'loterias' => $loterias,
            'loteria' => $loteria,
        ]);
    }

    #[Route('/conferir', name: 'conferir')]
    #[IsGranted('ROLE_ADMIN')]
    public function conferir(): Response
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $concursoRecuperarResultado = new ArrayInput([
            'command' => 'loteria:concurso:recuperar-resultado',
        ]);
        $apostaConferir = new ArrayInput([
            'command' => 'loteria:aposta:conferir',
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

    #[Route('/{uuid:loteria}/ultimo', name: 'loteria_ultimo', methods: ['GET'], requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function ultimo(Loteria $loteria): JsonResponse
    {
        $concurso = $this->concursoRepository->findUltimoConcursoByLoteria($loteria);

        if (null === $concurso) {
            return $this->json([
                'concurso' => '',
            ], Response::HTTP_NOT_FOUND
            );
        }

        return $this->json([
            'concurso' => $concurso->getNumero(),
        ], Response::HTTP_OK
        );
    }
}
