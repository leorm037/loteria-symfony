<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\DTO;

use App\Entity\Loteria;

class BolaoDTO
{

    private Loteria $loteria;
    private int $concursoNumero;
    private string $nome;
    private string $arquivoPlanilhaCsv;
    private string $arquivoComprovantePdf;

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

    public function getArquivoPlanilhaCsv(): string
    {
        return $this->arquivoPlanilhaCsv;
    }

    public function getArquivoComprovantePdf(): string
    {
        return $this->arquivoComprovantePdf;
    }

    public function setArquivoPlanilhaCsv(string $arquivoPlanilhaCsv): void
    {
        $this->arquivoPlanilhaCsv = $arquivoPlanilhaCsv;
    }

    public function setArquivoComprovantePdf(string $arquivoComprovantePdf): void
    {
        $this->arquivoComprovantePdf = $arquivoComprovantePdf;
    }
}
