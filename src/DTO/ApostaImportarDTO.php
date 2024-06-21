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

class ApostaImportarDTO
{
    private Loteria $loteria;
    private int $numero;
    private string $arquivoPlanilhaCsv;
    private string $arquivoComprovantePdf;

    public function getLoteria(): Loteria
    {
        return $this->loteria;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function getArquivoPlanilhaCsv(): string
    {
        return $this->arquivoPlanilhaCsv;
    }

    public function setLoteria(Loteria $loteria): void
    {
        $this->loteria = $loteria;
    }

    public function setNumero(int $numero): void
    {
        $this->numero = $numero;
    }

    public function setArquivoPlanilhaCsv(string $arquivoPlanilhaCsv): void
    {
        $this->arquivoPlanilhaCsv = $arquivoPlanilhaCsv;
    }

    public function getArquivoComprovantePdf(): string
    {
        return $this->arquivoComprovantePdf;
    }

    public function setArquivoComprovantePdf(string $arquivoComprovantePdf): void
    {
        $this->arquivoComprovantePdf = $arquivoComprovantePdf;
    }
}
