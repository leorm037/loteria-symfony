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

    public function getIterator(): Traversable
    {
        return $this->paginator->getIterator();
    }

    public function getPaginaQuantidade(): int
    {
        $registros = $this->count();
        
        if ($registros == 0) {
            return 0;
        }
        
        $registrosPorPagina = $this->registrosPorPagina;

        $paginas = intval($registros / $registrosPorPagina);
        
        if ($registros % $registrosPorPagina > 0) {
            $paginas++;
        }
        
        return $paginas;
    }

    public function getPaginas(): array
    {
        if ($this->getPaginaQuantidade() == 0) {
            return [];
        }
        
        return range(0, $this->getPaginaQuantidade() - 1, 1);
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
    
    public function getPaginaProxima(): int {
        return $this->getPaginaAtual() + 1;
    }
    
    public function getPaginaAnterior(): int {
        return $this->getPaginaAtual() - 1;
    }

}
