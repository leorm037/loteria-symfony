<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\DTO;

use ArrayIterator;
use Countable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<array>
 */
class PaginacaoDTO implements Countable, IteratorAggregate
{
    private int $paginasPorBloco = 3;

    /**
     * @param Paginator<mixed> $paginator
     */
    public function __construct(
        private Paginator $paginator,
        private int $registrosPorPagina,
        private int $paginaAtual,
    ) {
    }

    public function count(): int
    {
        return $this->paginator->count();
    }

    /**
     * @return ArrayIterator<int, mixed>
     */
    public function getIterator(): Traversable
    {
        return $this->paginator->getIterator();
    }

    public function getPaginasQuantidade(): int
    {
        $registros = $this->count();
        $registrosPorPagina = $this->registrosPorPagina;

        $paginasQuantidade = ceil($registros / $registrosPorPagina);

        return (int) $paginasQuantidade;
    }

    public function getBlocoQuantidade(): int
    {
        $paginaQuantidade = $this->getPaginasQuantidade();
        $paginasPorBloco = $this->paginasPorBloco;

        $blocoQuantidade = ceil($paginaQuantidade / $paginasPorBloco);

        return (int) $blocoQuantidade;
    }

    public function getPaginaAtual(): int
    {
        return $this->paginaAtual;
    }

    public function getBlocoAtual(): int
    {
        $paginaAtual = $this->getPaginaAtual();
        $paginasPorBloco = $this->paginasPorBloco;

        $blocoAtual = ($paginaAtual - 1) / $paginasPorBloco;

        return $blocoAtual;
    }

    /**
     * @return array<int,int>
     */
    public function getPaginas(): array
    {
        $blocoAtual = $this->getBlocoAtual();
        $paginasPorBloco = $this->paginasPorBloco;
        $paginasQuantidade = $this->getPaginasQuantidade();

        $paginaInicialBloco = ($blocoAtual * $paginasPorBloco) + 1;
        $paginaFinalBloco = min($paginaInicialBloco + $paginasPorBloco - 1, $paginasQuantidade);

        return range($paginaInicialBloco, $paginaFinalBloco, 1);
    }

    public function isBlocoPrimeiro(): bool
    {
        return 0 === $this->getBlocoAtual();
    }

    public function isPaginaPrimeira(): bool
    {
        return 1 === $this->getPaginaAtual();
    }

    public function getPaginaAnterior(): int
    {
        return $this->getPaginaAtual() - 1;
    }

    public function getPaginaProxima(): int
    {
        return $this->getPaginaAtual() + 1;
    }

    public function isPaginaUltima(): bool
    {
        return $this->getPaginasQuantidade() === $this->paginaAtual;
    }

    public function isBlocoUltimo(): bool
    {
        $blocoUltimo = $this->getBlocoQuantidade() - 1;

        return $blocoUltimo === $this->getBlocoAtual();
    }
}
