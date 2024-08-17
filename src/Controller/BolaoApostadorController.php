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
use App\Enum\TokenEnum;
use App\Form\ApostadorType;
use App\Repository\ApostadorRepository;
use App\Repository\ArquivoRepository;
use App\Repository\BolaoRepository;
use App\Security\Voter\ApostadorVoter;
use App\Service\ApostadorComprovanteJpgService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route(name: 'app_bolao_apostador_')]
class BolaoApostadorController extends AbstractController
{
    public function __construct(
        private BolaoRepository $bolaoRepository,
        private ApostadorRepository $apostadorRepository,
        private ApostadorComprovanteJpgService $apostadorComprovante,
        private ArquivoRepository $arquivoRepository
    ) {
    }

    #[Route('/bolao/{uuid}/apostador', name: 'index', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function index(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::LIST, $bolao);

        $apostadores = $this->apostadorRepository->findByBolao($bolao);

        return $this->render('bolao_apostador/index.html.twig', [
            'bolao' => $bolao,
            'apostadores' => $apostadores,
        ]);
    }

    #[Route('/bolao/{uuid}/apostador/new', name: 'new', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::NEW, $bolao);

        $apostador = new Apostador();
        $apostador->setBolao($bolao);

        $form = $this->createForm(ApostadorType::class, $apostador);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arquivoComprovanteJpg = $form->get('arquivoComprovanteJpg')->getData();

            $apostador
                    ->setArquivo($this->arquivarComprovante($arquivoComprovanteJpg))
            ;

            $this->apostadorRepository->save($apostador, true);

            $this->addFlash('success', \sprintf('Apostador "%s" foi cadastrador com sucesso.', $apostador->getNome()));

            return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolao_apostador/new.html.twig', [
            'form' => $form,
            'bolao' => $bolao,
        ]);
    }

    #[Route('/bolao/apostador/{uuid}/edit', name: 'edit', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $apostador = $this->apostadorRepository->findByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::EDIT, $apostador);

        $form = $this->createForm(ApostadorType::class, $apostador);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arquivoComprovanteJpg = $form->get('arquivoComprovanteJpg')->getData();

            if ($arquivoComprovanteJpg) {
                if ($apostador->getArquivo()) {
                    $this->deleteComprovante($apostador->getArquivo());
                }

                $apostador->setArquivo($this->arquivarComprovante($arquivoComprovanteJpg));
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
    public function delete(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $apostador = $this->apostadorRepository->findByUuid($uuid);

        $this->denyAccessUnlessGranted(ApostadorVoter::DELETE, $apostador);

        /** @var string|null $token */
        $token = $request->getPayload()->get('token');

        if (!$this->isCsrfTokenValid(TokenEnum::DELETE->value, $token)) {
            $this->addFlash('danger', 'Formulário de exclusão está inválido, tente novamente.');

            return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $apostador->getBolao()->getUuid()], Response::HTTP_SEE_OTHER);
        }

        if ($apostador->getArquivo()) {
            $this->deleteComprovante($apostador->getArquivo());
        }

        $this->apostadorRepository->delete($apostador);

        $this->addFlash('success', \sprintf('Apostador "%s" removido com sucesso.', $apostador->getNome()));

        return $this->redirectToRoute('app_bolao_apostador_index', ['uuid' => $apostador->getBolao()->getUuid()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/bolao/apostador/comprovante/{uuid}/download', name: 'comprovante_download', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'], methods: ['GET'])]
    public function comprovateDownload(Request $request): BinaryFileResponse
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $arquivo = $this->arquivoRepository->findByUuid($uuid);

        if (!file_exists($arquivo->getCaminhoNome())) {
            throw new NotFoundHttpException(\sprintf('Não foi possível encontrar o arquivo "%s".', $arquivo->getNomeOriginal()));
        }

        return $this->file($arquivo->getCaminhoNome(), $arquivo->getNomeOriginal(), ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function arquivarComprovante(?UploadedFile $arquivoComprovanteJpg): ?Arquivo
    {
        if (!$arquivoComprovanteJpg) {
            return null;
        }

        $caminhoNome = $this->apostadorComprovante->upload($arquivoComprovanteJpg);

        $arquivo = new Arquivo();
        $arquivo
                ->setNomeOriginal($arquivoComprovanteJpg->getClientOriginalName())
                ->setCaminhoNome($caminhoNome)
        ;

        $this->arquivoRepository->save($arquivo, true);

        return $arquivo;
    }

    private function deleteComprovante(Arquivo $arquivo): void
    {
        $this->apostadorComprovante->delete($arquivo->getCaminhoNome());
    }
}
