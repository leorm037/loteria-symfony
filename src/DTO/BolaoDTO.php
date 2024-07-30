<?php

namespace App\DTO;

use App\Entity\Loteria;

class BolaoDTO {

    private Loteria $loteria;
    private int $concursoNumero;
    private string $nome;

    public function getLoteria(): Loteria {
        return $this->loteria;
    }

    public function getConcursoNumero(): int {
        return $this->concursoNumero;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function setLoteria(Loteria $loteria): static {
        $this->loteria = $loteria;

        return $this;
    }

    public function setConcursoNumero(int $concursoNumero): static {
        $this->concursoNumero = $concursoNumero;

        return $this;
    }

    public function setNome(string $nome): static {
        $this->nome = $nome;

        return $this;
    }
}
