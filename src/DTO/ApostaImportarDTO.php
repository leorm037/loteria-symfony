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

use App\Entity\Bolao;

class ApostaImportarDTO
{
    private Bolao $bolao;
    private string $arquivoPlanilhaCsv;

    public function getBolao(): Bolao
    {
        return $this->bolao;
    }

    public function setBolao(Bolao $bolao): void
    {
        $this->bolao = $bolao;
    }

    public function getArquivoPlanilhaCsv(): string
    {
        return $this->arquivoPlanilhaCsv;
    }

    public function setArquivoPlanilhaCsv(string $arquivoPlanilhaCsv): void
    {
        $this->arquivoPlanilhaCsv = $arquivoPlanilhaCsv;
    }
}
