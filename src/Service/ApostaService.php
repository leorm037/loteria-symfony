<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Service;

use App\Entity\Aposta;
use App\Entity\Arquivo;
use App\Entity\Bolao;
use App\Helper\CsvReaderHelper;
use App\Repository\ApostaRepository;
use App\Service\Upload\ApostaPlanilhaCsvService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApostaService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApostaRepository $apostaRepository,
        private ValidatorInterface $validator,
        private ApostaPlanilhaCsvService $planilhaService,
    ) {
    }

    /**
     * @return array<int,array<string>>
     */
    public function importarPlanilhaCsv(Bolao $bolao): array
    {
        $apostasJaCadastradas = [];

        $csvReaderHelp = new CsvReaderHelper($bolao->getPlanilhaJogosCsv()->getCaminhoNome());

        $apostasCadastradas = $this->apostaRepository->findAllApostasByBolaoId($bolao->getId());

        foreach ($csvReaderHelp->getIterator() as $row) {
            $dezenas = array_map(function ($value) {return str_pad($value, 2, '00', \STR_PAD_LEFT); }, $row);

            if ($apostasCadastradas) {
                $jaCadastrda = $this->isApostaCadastrada($dezenas, $apostasCadastradas);

                if ($jaCadastrda) {
                    $apostasJaCadastradas[] = $dezenas;
                    continue;
                }
            }

            $aposta = new Aposta();
            $aposta
                    ->setDezenas($dezenas)
                    ->setBolao($bolao)
            ;

            $errors = $this->validator->validate($aposta);

            if (count($errors) > 0) {
                continue;
            }

            $this->entityManager->persist($aposta);
        }

        $this->entityManager->flush();

        return $apostasJaCadastradas;
    }

    public function anexarPlanilha(UploadedFile $arquivoPlanilhaCsv): Arquivo
    {
        $caminhoNome = $this->planilhaService->save($arquivoPlanilhaCsv);

        $arquivo = new Arquivo();

        return $arquivo
                        ->setNomeOriginal($arquivoPlanilhaCsv->getClientOriginalName())
                        ->setCaminhoNome($caminhoNome)
        ;
    }

    public function excluirPlanilha(?Arquivo $arquivo): void
    {
        if (null === $arquivo) {
            return;
        }

        $this->planilhaService->delete($arquivo->getCaminhoNome());
    }

    /**
     * @param array<string> $dezenas
     * @param Aposta[]      $apostasCadastradas
     */
    private function isApostaCadastrada(array $dezenas, array $apostasCadastradas): bool
    {
        /** @var Aposta $apostaCadastrada */
        foreach ($apostasCadastradas as $apostaCadastrada) {
            $diff = [];

            if (\count($dezenas) === \count($apostaCadastrada->getDezenas())) {
                $diff = array_diff($dezenas, $apostaCadastrada->getDezenas());
            }

            if (!$diff) {
                return true;
            }
        }

        return false;
    }
}
