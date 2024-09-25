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

use App\Entity\Aposta;
use App\Entity\Bolao;
use App\Enum\TokenEnum;
use App\Form\ApostaType;
use App\Repository\ApostaRepository;
use App\Security\Voter\ApostaVoter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

#[Route('/bolao', name: 'app_bolao_apostas_')]
class BolaoApostaController extends AbstractController
{

    public function __construct(
            private ApostaRepository $apostaRepository,
            private SluggerInterface $slugger,
    )
    {
        
    }

    #[Route('/{uuid:bolao}/apostas', name: 'index', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function index(Request $request, Bolao $bolao): Response
    {
        $registrosPorPaginas = $request->get('registros-por-pagina', 10);

        $pagina = $request->get('pagina', 1);

        $this->denyAccessUnlessGranted(ApostaVoter::LIST, $bolao);

        $apostas = $this->apostaRepository->findApostasByUuidBolao($bolao->getUuid(), $registrosPorPaginas, $pagina);

        return $this->render('bolao_aposta/index.html.twig', [
                    'apostas' => $apostas,
                    'bolao' => $bolao,
        ]);
    }

    #[Route('/{uuid:bolao}/apostas/new', name: 'new', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function new(Request $request, Bolao $bolao): Response
    {
        $this->denyAccessUnlessGranted(ApostaVoter::NEW, $bolao);

        $aposta = new Aposta();
        $aposta->setBolao($bolao);

        $form = $this->createForm(ApostaType::class, $aposta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->apostaRepository->save($aposta, true);

            $this->addFlash('success', \sprintf('Dezenas "%s" cadastradas com sucesso.', implode(', ', $aposta->getDezenas())));

            return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao_aposta/new.html.twig', [
                    'form' => $form,
                    'bolao' => $bolao,
        ]);
    }

    #[Route('/apostas/{uuid:aposta}/edit', name: 'edit', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Aposta $aposta): Response
    {
        $bolao = $aposta->getBolao();

        $this->denyAccessUnlessGranted(ApostaVoter::EDIT, $bolao);

        $form = $this->createForm(ApostaType::class, $aposta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->apostaRepository->save($aposta, true);

            $this->addFlash('success', \sprintf('Dezenas "%s" alteradas com sucesso.', implode(', ', $aposta->getDezenas())));

            return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao_aposta/edit.html.twig', [
                    'form' => $form,
                    'bolao' => $bolao,
        ]);
    }

    #[Route('/{uuid:bolao}/apostas/exportar', name: 'exportar', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function exportar(Bolao $bolao): StreamedResponse
    {
        $planilha = new Spreadsheet();
        $aba = $planilha->getActiveSheet();
        $aba->setCellValue('A1', 'Dezenas');
        $aba->setCellValue('B1', 'Conferida');
        $aba->setCellValue('C1', 'Acertos');
        $aba->setCellValue('D1', 'Atualização');

        $apostas = $this->apostaRepository->findAllApostasByUuidBolao($bolao->getUuid());

        for ($i = 0; $i < count($apostas); $i++) {
            $linha = $i + 2;

            /** @var Aposta $aposta */
            $aposta = $apostas[$i];

            $dezenas = implode(', ', $aposta->getDezenas());
            $conferida = ($aposta->isConferida()) ? 'Sim' : 'Não';
            $acertos = $aposta->getQuantidadeAcertos();
            $atualizacao = ($aposta->getUpdatedAt()) ? $aposta->getUpdatedAt()->format('d/m/Y H:i:s') : $aposta->getCreatedAt()->format('d/m/Y H:i:s');

            $aba->setCellValue('A' . $linha, $dezenas);
            $aba->setCellValue('B' . $linha, $conferida);
            $aba->setCellValue('C' . $linha, $acertos);
            $aba->setCellValue('D' . $linha, $atualizacao);
        }
        
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

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        $bolaoNomeArquivo . '.xlsx'
                ));

        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    #[Route('/apostas/delete', name: 'delete', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['POST'])]
    public function delete(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $aposta = $this->apostaRepository->findByUuid($uuid);

        $bolao = $aposta->getBolao();

        $this->denyAccessUnlessGranted(ApostaVoter::DELETE, $bolao);

        /** @var string|null $token */
        $token = $request->getPayload()->get('token');

        if (!$this->isCsrfTokenValid(TokenEnum::DELETE->value, $token)) {
            $this->addFlash('danger', 'Formulário de exclusão está inválido, tente novamente.');

            return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        $this->apostaRepository->delete($aposta);

        $this->addFlash('success', \sprintf('Aposta "%s" foi removida com sucesso.', implode(', ', $aposta->getDezenas())));

        return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
    }
}
