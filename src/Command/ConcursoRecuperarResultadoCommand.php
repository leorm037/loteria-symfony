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

use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Factory\ConcursoFactory;
use App\Repository\ConcursoRepository;
use App\Repository\LoteriaRepository;
use App\Service\ConcursoSorteioService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand(
    name: 'loteria:concurso:recuperar-resultado',
    description: 'Recuperar o resultado dos concursos sorteados.',
)]
class ConcursoRecuperarResultadoCommand extends Command
{
    /** @var array<int, array{status: string, message: string}> */
    private $messages = [];

    public function __construct(
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private LoteriaRepository $loteriaRepository,
        private ConcursoRepository $concursoRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
                ->addArgument(
                    'loteria',
                    InputArgument::OPTIONAL,
                    'Recupera o resultado da loteria informada'
                )
                ->addOption(
                    'concurso',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'Número do concuro para recuperar'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $loteriaSlug = $input->getArgument('loteria');
        $concursoNumero = $input->getOption('concurso');

        if ($loteriaSlug) {
            $this->recuperarConcursoLoteria($loteriaSlug, $concursoNumero);
        } else {
            $this->recuperarUltimoConcurso();
        }

        $this->exibirMensagens($io);

        return Command::SUCCESS;
    }

    private function recuperarUltimoConcurso(): void
    {
        $loterias = $this->loteriaRepository->findAllOrderByNome();

        if (null === $loterias) {
            $this->messages[] = ['status' => 'info', 'message' => 'Nenhuma loteria encontrada.'];
            $this->logger->info('Nenhuma loteria encontrada ao recuperar resultado dos concursos');

            return;
        }

        foreach ($loterias as $loteria) {
            $this->gravarSorteio($loteria);
        }
    }

    private function recuperarConcursoLoteria(string $loteriaSlug, ?int $concursoNumero = null): void
    {
        $loteria = $this->loteriaRepository->findBySlug($loteriaSlug);

        if (null === $loteria) {
            $this->messages[] = [
                'status' => 'danger',
                'message' => \sprintf('Loteria "%s" não foi encontrada.', $loteriaSlug),
            ];

            return;
        }

        $this->gravarSorteio($loteria, $concursoNumero);
    }

    private function gravarSorteio(Loteria $loteria, ?int $numero = null): void
    {
        $key = 'json_'.$loteria->getId().'_'.$numero;

        /** @var Concurso $sorteio */
        $sorteio = $this->cache->get($key, function (ItemInterface $item) use ($loteria, $numero) {
            $item->expiresAfter(new \DateInterval('P1D'));

            try {
                return ConcursoSorteioService::getConcurso($loteria, $numero);
            } catch (\Exception $e) {
                $this->messages[] = ['status' => 'error', 'message' => $e->getMessage()];
                $this->logger->info($e->getMessage());

                return null;
            }
        });

        if (null == $sorteio) {
            return;
        }

        $concurso = $this->concursoRepository->findByLoteriaAndNumero(
            $loteria,
            $sorteio->getNumero()
        );

        if (null === $concurso) {
            $loteria = $this->loteriaRepository->find($sorteio->getLoteria()->getId());

            $sorteio->setLoteria($loteria);

            $this->concursoRepository->save($sorteio, true);

            $this->messages[] = [
                'status' => 'success',
                'message' => \sprintf('O concurso %s da %s foi salvo.', $sorteio->getNumero(), $sorteio->getLoteria()->getNome()),
            ];
        } elseif (null === $concurso->getDezenas() || null === $concurso->getRateioPremio()) {
            ConcursoFactory::updateFromJson($concurso, $sorteio);

            $this->concursoRepository->save($concurso, true);

            $this->messages[] = [
                'status' => 'success',
                'message' => \sprintf('O concurso %s da %s foi atualizado.', $concurso->getNumero(), $concurso->getLoteria()->getNome()),
            ];
        } else {
            $this->messages[] = [
                'status' => 'info',
                'message' => \sprintf('O concurso %s da %s já estava salvo.', $concurso->getNumero(), $concurso->getLoteria()->getNome()),
            ];
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
