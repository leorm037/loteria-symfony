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

use App\Entity\Apostador;
use App\Entity\Arquivo;
use App\Entity\Bolao;
use App\Enum\TokenEnum;
use App\Form\ApostadorSelecionarType;
use App\Form\ApostadorType;
use App\Form\BolaoSelecionarType;
use App\Repository\ApostadorRepository;
use App\Repository\ArquivoRepository;
use App\Repository\BolaoRepository;
use App\Security\Voter\ApostadorVoter;
use App\Service\Upload\ApostadorComprovanteService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

#[Route(name: 'app_bolao_apostador_')]
class BolaoApostadorController extends AbstractController {

    public function __construct(
            private BolaoRepository $bolaoRepository,
            private SluggerInterface $slugger,
            private ApostadorRepository $apostadorRepository,
            private ApostadorComprovanteService $apostadorComprovante,
            private ArquivoRepository $arquivoRepository,
            private EntityManagerInterface $entityManager,
    ) {
        
    }

    #[Route('/bolao/{uuid}/apostador', name: 'index', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function index(Request $request): Response {
        $registrosPorPaginas = $request->get('registros-por-pagina', 10);

        $pagina = $request->get('pagina', 1);

        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::LIST, $bolao);

        $apostadores = $this->apostadorRepository->findByBolao($bolao, $registrosPorPaginas, $pagina);

        return $this->render('bolao_apostador/index.html.twig', [
                    'bolao' => $bolao,
                    'apostadores' => $apostadores,
        ]);
    }

    #[Route('/bolao/{uuid:bolao}/apostador/new', name: 'new', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function new(Request $request, Bolao $bolao): Response {
        $this->denyAccessUnlessGranted(ApostadorVoter::NEW, $bolao);

        $apostador = new Apostador();
        $apostador->setBolao($bolao);

        $form = $this->createForm(ApostadorType::class, $apostador);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arquivoComprovanteJpg = $form->get('arquivoComprovanteJpg')->getData();

            $apostador
                    ->setComprovantePagamento($this->arquivarComprovante($arquivoComprovanteJpg))
            ;

            $this->apostadorRepository->save($apostador, true);

            $this->addFlash('success', \sprintf('Apostador "%s" foi cadastrado com sucesso.', $apostador->getNome()));

            return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao_apostador/new.html.twig', [
                    'form' => $form,
                    'bolao' => $bolao,
        ]);
    }

    #[Route('/bolao/apostador/{uuid}/edit', name: 'edit', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function edit(Request $request): Response {
        $uuid = Uuid::fromString($request->get('uuid'));

        $apostador = $this->apostadorRepository->findByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::EDIT, $apostador);

        $form = $this->createForm(ApostadorType::class, $apostador);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arquivoComprovanteJpg = $form->get('arquivoComprovanteJpg')->getData();

            if ($arquivoComprovanteJpg) {
                if ($apostador->getComprovantePagamento()) {
                    $this->deleteComprovante($apostador->getComprovantePagamento());
                }

                $apostador->setComprovantePagamento($this->arquivarComprovante($arquivoComprovanteJpg));
                $apostador->setCotaPaga(true);
            }

            $this->apostadorRepository->save($apostador, true);

            $this->addFlash('success', \sprintf('Apostador "%s" foi alterado com sucesso.', $apostador->getNome()));

            return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $apostador->getBolao()->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao_apostador/edit.html.twig', [
                    'form' => $form,
                    'bolao' => $apostador->getBolao(),
        ]);
    }

    #[Route('/bolao/apostador/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request): Response {
        $uuid = Uuid::fromString($request->get('uuid'));

        $apostador = $this->apostadorRepository->findByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::DELETE, $apostador);

        /** @var string|null $token */
        $token = $request->getPayload()->get('token');

        if (!$this->isCsrfTokenValid(TokenEnum::DELETE->value, $token)) {
            $this->addFlash('danger', 'Formulário de exclusão está inválido, tente novamente.');

            return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $apostador->getBolao()->getUuid()], Response::HTTP_SEE_OTHER);
        }

        if ($apostador->getComprovantePagamento()) {
            $this->deleteComprovante($apostador->getComprovantePagamento());
        }

        $this->apostadorRepository->delete($apostador);

        $this->addFlash('success', \sprintf('Apostador "%s" removido com sucesso.', $apostador->getNome()));

        return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $apostador->getBolao()->getUuid()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/bolao/apostador/comprovante/{uuid}/download', name: 'comprovante_download', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function comprovateDownload(Request $request): BinaryFileResponse {
        $uuid = Uuid::fromString($request->get('uuid'));

        $arquivo = $this->arquivoRepository->findByUuid($uuid);

        if (!file_exists($arquivo->getCaminhoNome())) {
            throw new NotFoundHttpException(\sprintf('Não foi possível encontrar o arquivo "%s".', $arquivo->getNomeOriginal()));
        }

        return $this->file($arquivo->getCaminhoNome(), $arquivo->getNomeOriginal(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/bolao/{uuid:bolao}/apostador/importar-apostadores-selecionar-bolao', name: 'importar_apostadores_selecionar_bolao', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function importarApostadoresSeleconarBolao(Request $request, Bolao $bolao): Response {
        $form = $this->createForm(BolaoSelecionarType::class, null, ['bolao' => $bolao]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Bolao $bolaoSelecionado */
            $bolaoSelecionado = $form->get('bolao')->getData();

            return $this->redirectToRoute(
                            'app_bolao_apostador_importar_apostadores_selecionar_apostadores',
                            [
                                'uuidBolao' => $bolao->getUuid(),
                                'uuidBolaoSelecionado' => $bolaoSelecionado->getUuid(),
                            ],
                            Response::HTTP_SEE_OTHER
                    );
        }

        return $this->render('bolao_apostador/importar-apostadores-selecionar-bolao.html.twig', [
                    'bolao' => $bolao,
                    'form' => $form,
        ]);
    }

    #[Route('/bolao/{uuid:bolao}/apostador/exportar', name: 'exportar', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function exportar(Bolao $bolao): StreamedResponse {
        $planilha = new Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setCellValue('A1', 'Nome')
                ->setCellValue('B1', 'Pago')
                ->setCellValue('C1', 'Cotas')
                ->setCellValue('D1', 'Valor (R$)')
        ;

        $apostadores = $bolao->getApostadores();
        $valorCota = $bolao->getCotaValor();
        $numeroApostadores = count($apostadores);
        $totalApostador = 0;
        $linha = 0;

        for ($i = 0; $i < $numeroApostadores; $i++) {
            $linha = $i + 2;
            $valorApostado = 0;

            /** @var Apostador $apostador */
            $apostador = $apostadores[$i];

            if ($valorCota > 0) {
                $valorApostado = $valorCota * $apostador->getCotaQuantidade();
            }


            $aba
                    ->setCellValue('A' . $linha, $apostador->getNome())
                    ->setCellValue('B' . $linha, ($apostador->isCotaPaga()) ? 'Sim' : 'Não')
                    ->setCellValue('C' . $linha, $apostador->getCotaQuantidade())
                    ->setCellValue('D' . $linha, $valorApostado)
            ;

            $totalApostador += $valorApostado;
        }

        $linha++;

        $aba
                ->setCellValue('A' . $linha, 'Total')
                ->setCellValue('C' . $linha, $numeroApostadores)
                ->setCellValue('D' . $linha, $totalApostador)
        ;

        // Dimensionar colunas
        $aba->getColumnDimension('A')->setAutoSize(true);
        $aba->getColumnDimension('B')->setAutoSize(true);
        $aba->getColumnDimension('C')->setAutoSize(true);
        $aba->getColumnDimension('D')->setAutoSize(true);

        // Centralizar coluna
        $aba->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $response = new StreamedResponse(function () use ($planilha) {
                    $write = new Xlsx($planilha);
                    $write->save('php://output');
                });

        $bolaoNome = $bolao->getNome();
        $bolaoNomeArquivo = $this->slugger->slug($bolaoNome);
        
        $timeStamp = date('Y-m-d-His');

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        $bolaoNomeArquivo . '_apostadores_' . $timeStamp . '.xlsx'
                ));

        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    #[Route('/bolao/{uuidBolao}/apostador/importar-apostadores-selecionar-apostadores/{uuidBolaoSelecionado}', name: 'importar_apostadores_selecionar_apostadores', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}', 'uuidSelecionado' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function apostadorImportarSelecionarApostadores(Request $request): Response {
        $uuidBolao = $request->get('uuidBolao');
        $uuidBolaoSelecionado = $request->get('uuidBolaoSelecionado');

        $bolao = $this->bolaoRepository->findOneByUuid(Uuid::fromString($uuidBolao));
        $bolaoSelecionado = $this->bolaoRepository->findOneByUuid(Uuid::fromString($uuidBolaoSelecionado));

        $form = $this->createForm(ApostadorSelecionarType::class, null, ['bolaoSelecionado' => $bolaoSelecionado]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apostadores = $form->get('apostador')->getData();

            /** @var Apostador $apostador */
            foreach ($apostadores as $apostador) {
                $apostadorNovo = new Apostador();
                $apostadorNovo
                        ->setNome($apostador->getNome())
                        ->setEmail($apostador->getEmail())
                        ->setPix($apostador->getPix())
                        ->setBolao($bolao)
                ;

                $this->entityManager->persist($apostadorNovo);
            }

            $this->entityManager->flush();

            $this->addFlash('success', \sprintf('%s apostadores importados.', \count($apostadores)));

            return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao_apostador/importar-apostadores-selecionar-apostadores.html.twig', [
                    'bolao' => $bolao,
                    'bolaoSelecionado' => $bolaoSelecionado,
                    'form' => $form,
        ]);
    }

    private function arquivarComprovante(?UploadedFile $arquivoComprovanteJpg): ?Arquivo {
        if (!$arquivoComprovanteJpg) {
            return null;
        }

        $caminhoNome = $this->apostadorComprovante->save($arquivoComprovanteJpg);

        $arquivo = new Arquivo();
        $arquivo
                ->setNomeOriginal($arquivoComprovanteJpg->getClientOriginalName())
                ->setCaminhoNome($caminhoNome)
        ;

        $this->arquivoRepository->save($arquivo, true);

        return $arquivo;
    }

    private function deleteComprovante(Arquivo $arquivo): void {
        $this->apostadorComprovante->delete($arquivo->getCaminhoNome());
    }
}
