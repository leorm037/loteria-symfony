<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\DTO;

use App\Entity\Loteria;

class ApostaImportarDTO
{
    private Loteria $loteria;
    private int $numero;
    private string $arquivoPlanilhaCsv;
    
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
}
