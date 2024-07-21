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

use App\DTO\BolaoDTO;
use App\Entity\Bolao;
use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Form\BolaoType;
use App\Repository\BolaoRepository;
use App\Repository\ConcursoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bolao', name: 'app_bolao_')]
class BolaoController extends AbstractController
{

    public function __construct(
            private BolaoRepository $bolaoRepository,
            private ConcursoRepository $concursoRepository
    )
    {
        
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $boloes = $this->bolaoRepository->list();
        
        return $this->render('bolao/index.html.twig', [
                    'boloes' => $boloes,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $bolaoDTO = new BolaoDTO();

        $form = $this->createForm(BolaoType::class, $bolaoDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $concurso = $this->cadastraConcursoSeNaoExistir(
                    $bolaoDTO->getLoteria(),
                    $bolaoDTO->getConcursoNumero()
            );

            $bolao = new Bolao();
            $bolao
                    ->setConcurso($concurso)
                    ->setNome($bolaoDTO->getNome())
            ;

            $this->bolaoRepository->save($bolao, true);
            
            $this->addFlash('success', sprintf('Bolão "%s" salvo com sucesso!', $bolao->getNome()));

            return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao/new.html.twig', [
                    'form' => $form,
                    'bolao' => $bolaoDTO
        ]);
    }

    private function cadastraConcursoSeNaoExistir(Loteria $loteria, int $concursoNumero): Concurso
    {
        $concurso = $this->concursoRepository->findByLoteriaAndNumero($loteria, $concursoNumero);

        if (null == $concurso) {
            $concurso = new Concurso();
            $concurso
                    ->setLoteria($loteria)
                    ->setNumero($concursoNumero)
            ;

            $this->concursoRepository->save($concurso, true);
        }

        return $concurso;
    }
}
