<?php

namespace App\DTO;

use Countable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use IteratorAggregate;
use Traversable;

class PaginacaoDTO implements Countable, IteratorAggregate
{

    public function __construct(
            private Paginator $paginator,
            private int $registrosPorPagina,
            private int $paginaAtual
    )
    {
        
    }

    public function count(): int
    {
        if ($this->paginator->count() > 0) {
            return $this->paginator->count();
        }

        return 0;
    }

    public function getRegistrosPorPagina(): int
    {
        return $this->registrosPorPagina;
    }

    public function getIterator(): Traversable
    {
        return $this->paginator->getIterator();
    }

    public function getPaginaQuantidade(): int
    {
        $registros = $this->count();

        $registrosPorPagina = $this->getRegistrosPorPagina();

        return ceil($registros / $registrosPorPagina);
    }

    public function getPaginas(): array
    {
        $paginaAutal = $this->getPaginaAtual();
        $registrosPorPagina = $this->registrosPorPagina;
        $paginacaoAtual = intval(($paginaAutal - 1) / $registrosPorPagina);
        $paginasQuantidade = $this->getPaginaQuantidade();
        
        $inicio = ($paginacaoAtual * $registrosPorPagina) + 1;
        
        $fim = min($inicio + $registrosPorPagina - 1, $paginasQuantidade);
        
        return range($inicio, $fim, 1);
    }

    public function getPaginaAtual(): int
    {
        return $this->paginaAtual;
    }

    public function isPaginaPrimeira(): bool
    {
        return 0 === $this->getPaginaAtual();
    }

    public function isPaginaUltima(): bool
    {
        if (count($this->getPaginas()) == 0) {
            return true;
        }

        return (max($this->getPaginas())) === $this->getPaginaAtual();
    }

    public function getPaginaProxima(): int
    {
        return $this->getPaginaAtual() + 1;
    }

    public function getPaginaAnterior(): int
    {
        return $this->getPaginaAtual() - 1;
    }
}
