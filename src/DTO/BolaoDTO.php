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

use App\Entity\Loteria;

class BolaoDTO
{
    private Loteria $loteria;
    private int $concursoNumero;
    private string $nome;
    private string $cotaValor;

    public function getLoteria(): Loteria
    {
        return $this->loteria;
    }

    public function getConcursoNumero(): int
    {
        return $this->concursoNumero;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setLoteria(Loteria $loteria): static
    {
        $this->loteria = $loteria;

        return $this;
    }

    public function setConcursoNumero(int $concursoNumero): static
    {
        $this->concursoNumero = $concursoNumero;

        return $this;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCotaValor(): string
    {
        return $this->cotaValor;
    }

    public function setCotaValor(string $cotaValor): static
    {
        $this->cotaValor = $cotaValor;

        return $this;
    }
}
