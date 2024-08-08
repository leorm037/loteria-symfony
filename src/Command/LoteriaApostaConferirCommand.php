<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\Loteria;
use App\Repository\ApostaRepository;
use App\Repository\LoteriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'loteria:aposta:conferir',
    description: 'Confere as apostas dos concursos sorteados.',
)]
class LoteriaApostaConferirCommand extends Command
{
    /** @var array<int, array{status: string, message: string}> */
    private $messages = [];

    public function __construct(
        private LoteriaRepository $loteriaRepository,
        private ApostaRepository $apostaRepository,
        private EntityManagerInterface $manager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
                ->addArgument(
                    'loteria',
                    InputArgument::OPTIONAL,
                    'Confere as apostas dos concursos sorteados da loteria informada.')
                ->addOption(
                    'concurso',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'Confere as apostas do concurso informado. É obrigado informar tambem a loteria.')
                ->addOption(
                    'loterias',
                    'l',
                    InputOption::VALUE_NONE,
                    'Apresenta a lista de loterias.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $listarLoteria = $input->getOption('loterias');

        if ($listarLoteria) {
            $this->listarLoterias();
            $this->exibirMensagens($io);

            return Command::SUCCESS;
        }

        $this->conferirApostasLoteria();
        $this->exibirMensagens($io);

        return Command::SUCCESS;
    }

    private function conferirApostasLoteria(?Loteria $loteria = null): void
    {
        $loterias = [];

        if ($loteria) {
            $loterias[] = $loteria;
        } else {
            $loterias = $this->loteriaRepository->findAllOrderByNome();
        }

        $this->messages[] = ['status' => 'title', 'message' => 'Conferir apostas'];

        foreach ($loterias as $item) {
            $this->messages[] = ['status' => 'info', 'message' => $item->getNome()];

            $apostas = $this->apostaRepository->findNaoConferidasConcursoSorteado($item);

            foreach ($apostas as $aposta) {
                $resultado = array_intersect(
                    $aposta->getBolao()->getConcurso()->getDezenas(),
                    $aposta->getDezenas()
                );

                $aposta
                        ->setConferida(true)
                        ->setQuantidadeAcertos(\count($resultado))
                ;

                $this->manager->persist($aposta);
            }

            $this->manager->flush();

            $this->messages[] = ['status' => 'null', 'message' => \sprintf('Quantidade de apostas conferidas: %s', \count($apostas))];
        }
    }

    private function listarLoterias(): void
    {
        $loterias = $this->loteriaRepository->findAllOrderByNome();

        $this->messages[] = ['status' => 'title', 'message' => 'Loterias cadastradas'];

        foreach ($loterias as $loteria) {
            $this->messages[] = ['status' => null, 'message' => $loteria->getSlugUrl()];
        }
    }

    private function exibirMensagens(SymfonyStyle $io): void
    {
        foreach ($this->messages as $message) {
            switch ($message['status']) {
                case 'success':
                    $io->success($message['message']);
                    break;
                case 'danger':
                    $io->error($message['message']);
                    break;
                case 'title':
                    $io->title($message['message']);
                    break;
                case 'info':
                    $io->info($message['message']);
                    break;
                default:
                    $io->text($message['message']);
                    break;
            }
        }
    }
}
