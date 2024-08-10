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
use App\Entity\BolaoArquivo;
use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Form\BolaoType;
use App\Helper\CsvReaderHelper;
use App\Repository\ApostaRepository;
use App\Repository\ArquivoRepository;
use App\Repository\BolaoArquivoRepository;
use App\Repository\BolaoRepository;
use App\Repository\ConcursoRepository;
use App\Service\ApostaComprovantePdfService;
use App\Service\ApostaPlanilhaCsvService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/bolao', name: 'app_bolao_')]
class BolaoController extends AbstractController
{
    public function __construct(
        private BolaoRepository $bolaoRepository,
        private ConcursoRepository $concursoRepository,
        private ApostaComprovantePdfService $comprovantePdfService,
        private ApostaPlanilhaCsvService $planilhaCsvService,
        private ArquivoRepository $arquivoRepository,
        private ApostaRepository $apostaRepository,
        private EntityManagerInterface $entityManager,
        private BolaoArquivoRepository $bolaoArquivoRepository,
        private ValidatorInterface $validator
    ) {
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

            $this->bolaoRepository->save($bolao, true);

            if ($arquivoComprovantePdf) {
                $this->anexarComprovante($bolao, $arquivoComprovantePdf);
            }

            if ($arquivoPlanilhaCsv) {
                $this->anexarImportarPlanilha($bolao, $arquivoPlanilhaCsv);
            }

            $this->addFlash('success', \sprintf('Bolão "%s" salvo com sucesso!', $bolao->getNome()));

            return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao/new.html.twig', [
            'form' => $form,
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

            $arquivoComprovantePdf = $form->get('arquivoComprovantePdf')->getData();
            $arquivoPlanilhaCsv = $form->get('arquivoPlanilhaCsv')->getData();

            $bolao
                    ->setConcurso($concurso)
                    ->setNome($bolaoDTO->getNome())
            ;

            $this->bolaoRepository->save($bolao, true);

            if ($arquivoComprovantePdf) {
                $this->anexarComprovante($bolao, $arquivoComprovantePdf);
            }

            if ($arquivoPlanilhaCsv) {
                $this->anexarImportarPlanilha($bolao, $arquivoPlanilhaCsv);
            }

            $this->addFlash('success', \sprintf('Bolão "%s" alterado com sucesso!', $bolao->getNome()));

            return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        $uuidBolao = $request->request->get('uuid');

        $uuid = Uuid::fromString($uuidBolao);

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        if ($bolao) {
            $nomeBolao = $bolao->getNome();
            $this->excluirAnexos($bolao);
            $this->apostaRepository->deleteByBolao($bolao);
            $this->bolaoRepository->delete($bolao);
            $this->addFlash('success', \sprintf('Bolão "%s" excluido com sucesso.', $nomeBolao));
        }

        return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
    }

    private function anexarImportarPlanilha(Bolao $bolao, UploadedFile $arquivoPlanilhaCsv): void
    {
        $caminhoNome = $this->planilhaCsvService->upload($arquivoPlanilhaCsv);

        $arquivo = new Arquivo();
        $arquivo
                ->setNomeOriginal($arquivoPlanilhaCsv->getClientOriginalName())
                ->setCaminhoNome($caminhoNome)
        ;

        $this->arquivoRepository->save($arquivo, true);
        $this->importarPlanilha($bolao, $caminhoNome);
        $this->anexarArquivo($bolao, $arquivo);
    }

    public function importarPlanilha(Bolao $bolao, string $caminhoNome): void
    {
        $csvReaderHelp = new CsvReaderHelper($caminhoNome);

        $apostasCadastradas = $this->apostaRepository->findApostasByUuidBolao($bolao->getUuid());

        foreach ($csvReaderHelp->getIterator() as $row) {
            $dezenas = array_map('intval', $row);

            if (\count($apostasCadastradas) > 0) {
                $diferenca = [];

                foreach ($apostasCadastradas as $apostaCadastrada) {
                    if (\count($dezenas) == \count($apostaCadastrada->getDezenas())) {
                        $diferenca = array_diff($dezenas, $apostaCadastrada->getDezenas());

                        if (0 == \count($diferenca)) {
                            $this->addFlash('danger', \sprintf('A aposta "%s" já está cadastrada.', implode(', ', $dezenas)));
                            break;
                        }
                    }
                }

                if (0 == \count($diferenca)) {
                    continue;
                }
            }

            $aposta = new Aposta();
            $aposta
                    ->setDezenas($dezenas)
                    ->setBolao($bolao)
            ;

            $errors = $this->validator->validate($aposta);

            if (\count($errors) > 0) {
                /** @var ConstraintViolation $error */
                foreach ($errors as $error) {
                    $this->addFlash('danger', \sprintf('A aposta "%s" é inválida. '.$error->getMessage(), implode(', ', $aposta->getDezenas())));
                }
                continue;
            }

            $this->entityManager->persist($aposta);
        }

        $this->entityManager->flush();
    }

    private function anexarComprovante(Bolao $bolao, UploadedFile $arquivoComprovantePdf): void
    {
        $caminhoNome = $this->comprovantePdfService->upload($arquivoComprovantePdf);

        $arquivo = new Arquivo();
        $arquivo
                ->setNomeOriginal($arquivoComprovantePdf->getClientOriginalName())
                ->setCaminhoNome($caminhoNome)
        ;

        $this->arquivoRepository->save($arquivo, true);
        $this->anexarArquivo($bolao, $arquivo);
    }

    private function anexarArquivo(Bolao $bolao, Arquivo $arquivo): void
    {
        $bolaoArquivo = new BolaoArquivo();
        $bolaoArquivo
                ->setBolao($bolao)
                ->setArquivo($arquivo)
        ;

        $this->bolaoArquivoRepository->save($bolaoArquivo, true);
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

    private function excluirAnexos(Bolao $bolao): void
    {
        $bolaoArquivos = $this->bolaoArquivoRepository->findByBolao($bolao);

        foreach ($bolaoArquivos as $bolaoArquivo) {
            $arquivo = $bolaoArquivo->getArquivo();

            $this->bolaoArquivoRepository->delete($bolaoArquivo);

            if (file_exists($arquivo->getCaminhoNome())) {
                unlink($arquivo->getCaminhoNome());
            }

            $this->arquivoRepository->delete($arquivo);
        }
    }
}
