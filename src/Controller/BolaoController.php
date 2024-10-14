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
use App\Entity\Arquivo;
use App\Entity\Bolao;
use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Enum\TokenEnum;
use App\Form\BolaoType;
use App\Repository\ApostadorRepository;
use App\Repository\ApostaRepository;
use App\Repository\BolaoRepository;
use App\Repository\ConcursoRepository;
use App\Repository\LoteriaRepository;
use App\Repository\UsuarioRepository;
use App\Security\Voter\BolaoVoter;
use App\Service\ApostaService;
use App\Service\Upload\ApostaComprovanteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/bolao', name: 'app_bolao_')]
class BolaoController extends AbstractController
{
    public function __construct(
        private BolaoRepository $bolaoRepository,
        private ConcursoRepository $concursoRepository,
        private ApostaComprovanteService $comprovanteService,
        private ApostaService $apostaService,
        private ApostaRepository $apostaRepository,
        private ApostadorRepository $apostadorRepository,
        private UsuarioRepository $usuarioRepository,
        private LoteriaRepository $loteriaRepository,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $registrosPorPaginas = $request->get('registros-por-pagina', 10);

        $pagina = $request->get('pagina', 1);

        $filter_loteria = $request->get('filter_loteria', null);
        $filter_loteria_sanitized = ('' !== $filter_loteria) ? $filter_loteria : null;

        $filter_concurso = $request->get('filter_concurso', null);
        $filter_concurso_sanitized = ('' !== $filter_concurso) ? $filter_concurso : null;

        $filter_bolao = $request->get('filter_bolao', null);
        $filter_bolao_sanitized = ('' !== $filter_bolao) ? $filter_bolao : null;

        $filter_apurado = $request->get('filter_apurado', null);
        $filter_apurado_sanitized = ('' !== $filter_apurado) ? $filter_apurado : null;

        $usuarioEmail = $this->getUser()->getUserIdentifier();
        $usuario = $this->usuarioRepository->findByEmail($usuarioEmail);

        $boloes = $this->bolaoRepository->list(
            $usuario,
            $registrosPorPaginas,
            $pagina,
            $filter_loteria_sanitized,
            $filter_concurso_sanitized,
            $filter_bolao_sanitized,
            $filter_apurado_sanitized
        );

        $loterias = $this->loteriaRepository->findAllOrderByNome();

        return $this->render('bolao/index.html.twig', [
            'boloes' => $boloes,
            'loterias' => $loterias,
            'filter_loteria' => $filter_loteria,
            'filter_concurso' => $filter_concurso,
            'filter_bolao' => $filter_bolao,
            'filter_apurado' => $filter_apurado,
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

            $arquivoComprovante = $form->get('arquivoComprovante')->getData();
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

            if ($arquivoComprovante) {
                $bolao->setComprovanteJogos($this->anexarComprovante($arquivoComprovante));
            }

            if ($arquivoPlanilhaCsv) {
                $bolao->setPlanilhaJogosCsv($this->apostaService->anexarPlanilha($arquivoPlanilhaCsv));
            }

            $this->bolaoRepository->save($bolao, true);

            if ($arquivoPlanilhaCsv) {
                $this->apostaService->importarPlanilhaCsv($bolao);
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

            $arquivoComprovante = $form->get('arquivoComprovante')->getData();
            $arquivoPlanilhaCsv = $form->get('arquivoPlanilhaCsv')->getData();

            $bolao
                    ->setConcurso($concurso)
                    ->setNome($bolaoDTO->getNome())
                    ->setCotaValor($bolaoDTO->getCotaValor())
            ;

            if ($arquivoComprovante) {
                $this->excluirComprovante($bolao->getComprovanteJogos());
                $bolao->setComprovanteJogos(
                    $this->anexarComprovante($arquivoComprovante)
                );
            }

            if ($arquivoPlanilhaCsv) {
                $this->apostaService->excluirPlanilha($bolao->getPlanilhaJogosCsv());
                $bolao->setPlanilhaJogosCsv(
                    $this->apostaService->anexarPlanilha($arquivoPlanilhaCsv)
                );
            }

            $this->bolaoRepository->save($bolao, true);

            if ($arquivoPlanilhaCsv) {
                $dezenasJaCadastradas = $this->apostaService->importarPlanilhaCsv($bolao);
                $this->alertaApostasJaCadastradas($dezenasJaCadastradas);
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
            $this->excluirComprovante($bolao->getComprovanteJogos());
            $this->apostaService->excluirPlanilha($bolao->getPlanilhaJogosCsv());
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

        $arquivo = $bolao->getComprovanteJogos();

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

    private function anexarComprovante(UploadedFile $arquivoComprovantePdf): Arquivo
    {
        $caminhoNome = $this->comprovanteService->save($arquivoComprovantePdf);

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

        $this->comprovanteService->delete($arquivo->getCaminhoNome());
    }

    /**
     * @param array<int,array<string>>|null $listaDezenas
     */
    private function alertaApostasJaCadastradas(?array $listaDezenas): void
    {
        if (!$listaDezenas) {
            return;
        }

        foreach ($listaDezenas as $dezenas) {
            $message = \sprintf('Dezenas "%s" já cadastradas.', implode(', ', $dezenas));
            $this->addFlash('warning', $message);
        }
    }
}
