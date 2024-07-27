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

use App\DTO\ApostaImportarDTO;
use App\Entity\Aposta;
use App\Entity\Arquivo;
use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Enum\ArquivoTipoEnum;
use App\Form\ApostaImportarType;
use App\Helper\CsvReaderHelper;
use App\Repository\ApostaRepository;
use App\Repository\ArquivoRepository;
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

#[Route('/bolao', name: 'app_bolao_apostas_')]
class BolaoApostaController extends AbstractController
{

    public function __construct(
            private ApostaComprovantePdfService $comprovanteUpload,
            private ApostaPlanilhaCsvService $planilhaUpload,
            private EntityManagerInterface $entityManager,
            private ConcursoRepository $concursoRepository,
            private ValidatorInterface $validator,
            private ArquivoRepository $arquivoRepository,
            private ApostaRepository $apostaRepository,
            private BolaoRepository $bolaoRepository
    )
    {
        
    }

    #[Route('/{uuid}/apostas', name: 'index', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function index(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);

        $apostas = $this->apostaRepository->findApostasByUuidBolao($uuid);

        return $this->render('bolaoAposta/index.html.twig', [
                    'apostas' => $apostas,
                    'bolao' => $bolao,
        ]);
    }

    #[Route('/{uuid}/apostas/importar', name: 'importar', requirements: ['uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function importar(Request $request): Response
    {
        $uuid = Uuid::fromString($request->get('uuid'));

        $bolao = $this->bolaoRepository->findOneByUuid($uuid);
        
        $apostaImportar = new ApostaImportarDTO();

        $form = $this->createForm(ApostaImportarType::class, $apostaImportar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $planilhaArquivo = $form->get('arquivoPlanilhaCsv')->getData();

            if ($planilhaArquivo) {
                $planilha = $this->planilhaSalvar($planilhaArquivo);

                $csvReaderHelp = new CsvReaderHelper($planilha->getCaminhoNome());

                foreach ($csvReaderHelp->getIterator() as $row) {
                    $dezenas = array_map('intval', $row);
                    
                    $aposta = new Aposta();
                    $aposta
                            ->setBolao($bolao)
                            ->setDezenas($dezenas)
                    ;
                    
                    $errors = $this->validator->validate($aposta);
                    
                    if (count($errors) > 0) {
                        
                        /** @var ConstraintViolation $error */
                        foreach ($errors as $error) {
                            $this->addFlash('danger', $error->getMessage());
                        }
                        continue;
                    }

                    $this->entityManager->persist($aposta);
                }
                
                $this->entityManager->flush();
            }

            $this->addFlash('success', 'Planilha importada com sucesso!');

            return $this->redirectToRoute('app_bolao_apostas_index', ['uuid' => $bolao->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bolaoAposta/importar.html.twig', [
                    'form' => $form,
                    'apostaImportar' => $apostaImportar,
                    'bolao' => $bolao
        ]);
    }

    private function recuperarOuCadastrarConcurso(Loteria $loteria, int $numero): Concurso
    {
        $concurso = $this->concursoRepository->findByLoteriaAndNumero($loteria, $numero);

        if (null === $concurso) {
            $concurso = new Concurso();
            $concurso
                    ->setLoteria($loteria)
                    ->setNumero($numero)
            ;

            $this->concursoRepository->save($concurso, true);
        }

        return $concurso;
    }

    private function comprovanteSalvar(UploadedFile $comprovante): ?Arquivo
    {
        $comprovanteCaminhoNome = $this->comprovanteUpload->upload($comprovante);
        $arquivoTipo = $this->arquivoTipoRepository->findByNome(ArquivoTipoEnum::APOSTA_COMPROVANTE->value);

        $arquivo = new Arquivo();
        $arquivo
                ->setNome($comprovante->getClientOriginalName())
                ->setCaminhoNome($comprovanteCaminhoNome)
        ;

        $this->arquivoRepository->save($arquivo, true);

        return $arquivo;
    }

    private function planilhaSalvar(UploadedFile $planilha): ?Arquivo
    {
        $planilhaCaminhoNome = $this->planilhaUpload->upload($planilha);

        $arquivo = new Arquivo();
        $arquivo->setCaminhoNome($planilhaCaminhoNome);

        //$this->arquivoRepository->save($arquivo, true);

        return $arquivo;
    }
}
