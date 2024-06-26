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
use App\Repository\ArquivoTipoRepository;
use App\Repository\ConcursoRepository;
use App\Service\ApostaComprovantePdfService;
use App\Service\ApostaPlanilhaCsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/aposta', name: 'app_aposta_')]
class ApostaController extends AbstractController
{

    public function __construct(
            private ApostaPlanilhaCsvService $planilhaUpload,
            private ApostaComprovantePdfService $comprovanteUpload,
            private ConcursoRepository $concursoRepository,
            private ArquivoTipoRepository $arquivoTipoRepository,
            private ArquivoRepository $arquivoRepository,
            private ApostaRepository $apostaRepository
    )
    {
        
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $apostas = $this->apostaRepository->findMinhasApostas();
        
        return $this->render('aposta/index.html.twig', [
                    'apostas' => $apostas,
        ]);
    }

    #[Route('/importar', name: 'importar')]
    public function importar(Request $request): Response
    {
        $apostaImportar = new ApostaImportarDTO();

        $form = $this->createForm(ApostaImportarType::class, $apostaImportar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $concurso = $this->recuperarOuCadastrarConcurso($apostaImportar->getLoteria(), $apostaImportar->getNumero());

            $planilhaArquivo = $form->get('arquivoPlanilhaCsv')->getData();
            $comprovanteArquivo = $form->get('arquivoComprovantePdf')->getData();

            if ($comprovanteArquivo) {
                $comprovante = $this->comprovanteSalvar($comprovanteArquivo);
            } else {
                $comprovante = null;
            }

            if ($planilhaArquivo) {
                $planilha = $this->planilhaSalvar($planilhaArquivo);

                $csvReaderHelp = new CsvReaderHelper($planilha->getCaminhoNome());

                foreach ($csvReaderHelp->getIterator() as $row) {
                    $dezenas = array_map('intval', $row);
                    $aposta = new Aposta();
                    $aposta
                            ->setDezenas($dezenas)
                            ->setConcurso($concurso)
                            ->setPlanilha($planilha)
                    ;
                    
                    if($comprovante) {
                        $aposta->setComprovante($comprovante);
                    }

                    $this->apostaRepository->save($aposta, $csvReaderHelp->eof());
                }
            }
            
            $this->addFlash('success', 'Planilha importada com sucesso!');

            return $this->redirectToRoute('app_aposta_importar', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('aposta/importar.html.twig', [
                    'form' => $form,
                    'apostaImportar' => $apostaImportar,
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
        $arquivo->setCaminhoNome($comprovanteCaminhoNome)
                ->setArquivoTipo($arquivoTipo)
        ;
        
        $this->arquivoRepository->save($arquivo, true);
        
        return $arquivo;
    }
    
    private function planilhaSalvar(UploadedFile $planilha): ?Arquivo
    {
        $planilhaCaminhoNome = $this->planilhaUpload->upload($planilha);
        $arquivoTipo = $this->arquivoTipoRepository->findByNome(ArquivoTipoEnum::APOSTA_PLANILHA->value);

        $arquivo = new Arquivo();
        $arquivo->setCaminhoNome($planilhaCaminhoNome)
                ->setArquivoTipo($arquivoTipo)
        ;
        
        $this->arquivoRepository->save($arquivo, true);
        
        return $arquivo;
    }
}
