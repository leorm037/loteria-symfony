<?php

namespace App\DTO;

class BolaoResultadoPremiacao
{

    private int $acertos = 0;
    private int $quantidaDeJogos = 0;
    private float $valorPremio = 0.0;

    public function getAcertos(): int
    {
        return $this->acertos;
    }

    public function setAcertos(int $acertos): static
    {
        $this->acertos = $acertos;

        return $this;
    }

    public function getQuantidaDeJogos(): int
    {
        return $this->quantidaDeJogos;
    }

    public function sumQuantidaDeJogos(int $quantidaDeJogos): static
    {
        $this->quantidaDeJogos = $this->quantidaDeJogos + $quantidaDeJogos;

        return $this;
    }

    public function getValorPremioTotal(): float
    {
        return $this->valorPremio * $this->quantidaDeJogos;
    }

    public function setValorPremio(float $valorPremio): void
    {
        $this->valorPremio = $valorPremio;
    }
}
