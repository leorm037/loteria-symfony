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
use App\Enum\TokenEnum;
use App\Form\BolaoType;
use App\Helper\CsvReaderHelper;
use App\Repository\ApostadorRepository;
use App\Repository\ApostaRepository;
use App\Repository\BolaoRepository;
use App\Repository\ConcursoRepository;
use App\Repository\UsuarioRepository;
use App\Security\Voter\BolaoVoter;
use App\Service\ApostaComprovantePdfService;
use App\Service\ApostaPlanilhaCsvService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        private ApostaRepository $apostaRepository,
        private ApostadorRepository $apostadorRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private UsuarioRepository $usuarioRepository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $registrosPorPaginas = $request->get('registros-por-pagina', 10);

        $pagina = $request->get('pagina', 0);

        $usuarioEmail = $this->getUser()->getUserIdentifier();

        $usuario = $this->usuarioRepository->findByEmail($usuarioEmail);

        $boloes = $this->bolaoRepository->list($usuario, $registrosPorPaginas, $pagina);

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

            $usuarioEmail = $this->getUser()->getUserIdentifier();
            $usuario = $this->usuarioRepository->findByEmail($usuarioEmail);

            $bolao = new Bolao();
            $bolao
                    ->setConcurso($concurso)
                    ->setNome($bolaoDTO->getNome())
                    ->setUsuario($usuario)
                    ->setCotaValor($bolaoDTO->getCotaValor())
            ;

            if ($arquivoComprovantePdf) {
                $bolao->setComprovanteJogosPdf($this->anexarComprovante($arquivoComprovantePdf));
            }

            if ($arquivoPlanilhaCsv) {
                $bolao->setPlanilhaJogosCsv($this->anexarPlanilha($arquivoPlanilhaCsv));
            }

            $this->bolaoRepository->save($bolao, true);

            if ($arquivoPlanilhaCsv) {
                $this->importarPlanilha($bolao);
            }

            $this->addFlash('success', \sprintf('Bolão "%s" cadastrado com sucesso!', $bolao->getNome()));

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

        $this->denyAccessUnlessGranted(BolaoVoter::EDIT, $bolao);

        $bolaoDTO = new BolaoDTO();
        $bolaoDTO
                ->setNome($bolao->getNome())
                ->setLoteria($bolao->getConcurso()->getLoteria())
                ->setConcursoNumero($bolao->getConcurso()->getNumero())
                ->setCotaValor($bolao->getCotaValor())
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
                    ->setCotaValor($bolaoDTO->getCotaValor())
            ;

            if ($arquivoComprovantePdf) {
                $this->excluirComprovante($bolao->getComprovanteJogosPdf());
                $bolao->setComprovanteJogosPdf(
                    $this->anexarComprovante($arquivoComprovantePdf)
                );
            }

            if ($arquivoPlanilhaCsv) {
                $this->excluirPlanilha($bolao->getPlanilhaJogosCsv());
                $bolao->setPlanilhaJogosCsv(
                    $this->anexarPlanilha($arquivoPlanilhaCsv)
                );
            }

            $this->bolaoRepository->save($bolao, true);

            if ($arquivoPlanilhaCsv) {
                $this->importarPlanilha($bolao);
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

        /** @var string|null $token */
        $token = $request->getPayload()->get('token');

        if (!$this->isCsrfTokenValid(TokenEnum::DELETE->value, $token)) {
            $this->addFlash('danger', 'Formulário de exclusão inválido, tente novamente.');

            return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($bolao) {
            $nomeBolao = $bolao->getNome();
            $this->excluirComprovante($bolao->getComprovanteJogosPdf());
            $this->excluirPlanilha($bolao->getPlanilhaJogosCsv());
            $this->apostaRepository->deleteByBolao($bolao);
            $this->apostadorRepository->deleteByBolao($bolao);
            $this->bolaoRepository->delete($bolao);
            $this->addFlash('success', \sprintf('Bolão "%s" excluido com sucesso.', $nomeBolao));
        }

        return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{uuid}/comprovante/download', name: 'comprovante_download', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function comprovateDownload(Request $request): BinaryFileResponse
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $arquivo = $bolao->getComprovanteJogosPdf();

        if (!file_exists($arquivo->getCaminhoNome())) {
            throw new NotFoundHttpException(\sprintf('Não foi possível encontrar o arquivo "%s".', $arquivo->getNomeOriginal()));
        }

        return $this->file($arquivo->getCaminhoNome(), $arquivo->getNomeOriginal(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/{uuid}/planilha/download', name: 'planilha_download', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function planilhaDownload(Request $request): BinaryFileResponse
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $arquivo = $bolao->getPlanilhaJogosCsv();

        if (!file_exists($arquivo->getCaminhoNome())) {
            throw new NotFoundHttpException(\sprintf('Não foi possível encontrar o arquivo "%s".', $arquivo->getNomeOriginal()));
        }

        return $this->file($arquivo->getCaminhoNome(), $arquivo->getNomeOriginal(), ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function anexarPlanilha(UploadedFile $arquivoPlanilhaCsv): Arquivo
    {
        $caminhoNome = $this->planilhaCsvService->upload($arquivoPlanilhaCsv);

        $arquivo = new Arquivo();

        return $arquivo
                        ->setNomeOriginal($arquivoPlanilhaCsv->getClientOriginalName())
                        ->setCaminhoNome($caminhoNome)
        ;
    }

    public function importarPlanilha(Bolao $bolao): void
    {
        $csvReaderHelp = new CsvReaderHelper($bolao->getPlanilhaJogosCsv()->getCaminhoNome());

        $apostasCadastradas = [];

        if ($bolao->getUuid()) {
            $apostasCadastradas = $this->apostaRepository->findApostasByUuidBolao($bolao->getUuid());
        }

        foreach ($csvReaderHelp->getIterator() as $row) {
            $dezenas = array_map('strval', $row);

            if (\count($apostasCadastradas) > 0) {
                $diferenca = [];

                /** @var Aposta $apostaCadastrada */
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

    private function anexarComprovante(UploadedFile $arquivoComprovantePdf): Arquivo
    {
        $caminhoNome = $this->comprovantePdfService->upload($arquivoComprovantePdf);

        $arquivo = new Arquivo();

        return $arquivo
                        ->setNomeOriginal($arquivoComprovantePdf->getClientOriginalName())
                        ->setCaminhoNome($caminhoNome)
        ;
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

    private function excluirComprovante(?Arquivo $arquivo): void
    {
        if (null === $arquivo) {
            return;
        }

        $this->comprovantePdfService->delete($arquivo->getCaminhoNome());
    }

    private function excluirPlanilha(?Arquivo $arquivo): void
    {
        if (null === $arquivo) {
            return;
        }

        $this->planilhaCsvService->delete($arquivo->getCaminhoNome());
    }
}
