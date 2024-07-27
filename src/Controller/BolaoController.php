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
use App\Entity\Aposta;
use App\Entity\Arquivo;
use App\Entity\Bolao;
use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Form\BolaoType;
use App\Helper\CsvReaderHelper;
use App\Repository\ApostaRepository;
use App\Repository\ArquivoRepository;
use App\Repository\BolaoRepository;
use App\Repository\ConcursoRepository;
use App\Service\ApostaComprovantePdfService;
use App\Service\ApostaPlanilhaCsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/bolao', name: 'app_bolao_')]
class BolaoController extends AbstractController
{

    public function __construct(
            private BolaoRepository $bolaoRepository,
            private ConcursoRepository $concursoRepository,
            private ApostaComprovantePdfService $comprovantePdfService,
            private ApostaPlanilhaCsvService $planilhaCsvService,
            private ArquivoRepository $arquivoRepository,
            private ApostaRepository $apostaRepository
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

            $arquivoComprovantePdf = $form->get('arquivoComprovantePdf')->getData();
            $arquivoPlanilhaCsv = $form->get('arquivoPlanilhaCsv')->getData();

            $bolao = new Bolao();
            $bolao
                    ->setConcurso($concurso)
                    ->setNome($bolaoDTO->getNome())
            ;

            if ($arquivoComprovantePdf) {
                $bolao->setArquivoComprovantePdf($this->arquivarComprovante($arquivoComprovantePdf));
            }

            if ($arquivoPlanilhaCsv) {
                $bolao->setArquivoPlanilhaCsv($this->arquivarPlanilha($arquivoPlanilhaCsv));
            }

            $this->bolaoRepository->save($bolao, true);

            if ($bolao->getArquivoPlanilhaCsv()) {
                $this->importarApostas($bolao);
            }

            $this->addFlash('success', sprintf('Bolão "%s" salvo com sucesso!', $bolao->getNome()));

            return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao/new.html.twig', [
                    'form' => $form
        ]);
    }

    #[Route('/{uuid}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function edit(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $bolaoDTO = new BolaoDTO();
        $bolaoDTO
                ->setNome($bolao->getNome())
                ->setLoteria($bolao->getConcurso()->getLoteria())
                ->setConcursoNumero($bolao->getConcurso()->getNumero())
        ;

        $form = $this->createForm(BolaoType::class, $bolaoDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $concurso = $this->cadastraConcursoSeNaoExistir(
                    $bolaoDTO->getLoteria(),
                    $bolaoDTO->getConcursoNumero()
            );

            /** @var UploadedFile $arquivoComprovantePdf */
            $arquivoComprovantePdf = $form->get('arquivoComprovantePdf')->getData();
            /** @var UploadedFile $arquivoPlanilhaCsv */
            $arquivoPlanilhaCsv = $form->get('arquivoPlanilhaCsv')->getData();

            $bolao
                    ->setConcurso($concurso)
                    ->setNome($bolaoDTO->getNome())
            ;

            if ($arquivoComprovantePdf) {
                if ($bolao->getArquivoComprovantePdf()) {
                    $this->comprovantePdfService->delete($bolao->getArquivoComprovantePdf()->getCaminhoNome());
                }
                $bolao->setArquivoComprovantePdf($this->arquivarComprovante($arquivoComprovantePdf));
            }

            if ($arquivoPlanilhaCsv) {
                if ($bolao->getArquivoPlanilhaCsv()) {
                    $this->planilhaCsvService->delete($bolao->getArquivoPlanilhaCsv()->getCaminhoNome());
                }
                $bolao->setArquivoPlanilhaCsv($this->arquivarPlanilha($arquivoPlanilhaCsv));
            }

            $this->bolaoRepository->save($bolao, true);

            if ($bolao->getArquivoPlanilhaCsv()) {
                $this->importarApostas($bolao);
            }

            $this->addFlash('success', sprintf('Bolão "%s" alterado com sucesso!', $bolao->getNome()));

            return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao/edit.html.twig', [
                    'form' => $form
        ]);
    }

    private function arquivarComprovante(UploadedFile $comprovante): Arquivo
    {
        $comprovanteCaminho = $this->comprovantePdfService->upload($comprovante);

        $arquivo = new Arquivo();
        $arquivo
                ->setNomeOriginal($comprovante->getClientOriginalName())
                ->setCaminhoNome($comprovanteCaminho)
        ;

        $this->arquivoRepository->save($arquivo);

        return $arquivo;
    }

    private function arquivarPlanilha(UploadedFile $planilha): Arquivo
    {
        $planilhaCaminho = $this->planilhaCsvService->upload($planilha);

        $arquivo = new Arquivo();
        $arquivo
                ->setNomeOriginal($planilha->getClientOriginalName())
                ->setCaminhoNome($planilhaCaminho)
        ;

        $this->arquivoRepository->save($arquivo);

        return $arquivo;
    }

    public function importarApostas(Bolao $bolao)
    {
        $csvReaderHelp = new CsvReaderHelper($bolao->getArquivoPlanilhaCsv()->getCaminhoNome());

        foreach ($csvReaderHelp->getIterator() as $row) {
            $dezenas = array_map('intval', $row);
            $aposta = new Aposta();
            $aposta
                    ->setDezenas($dezenas)
                    ->setBolao($bolao)
            ;

            $this->apostaRepository->save($aposta, $csvReaderHelp->eof());
        }
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
