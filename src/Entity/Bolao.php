<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\BolaoRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BolaoRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Bolao extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    protected ?Uuid $uuid = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Informe o nome do bolão.')]
    private ?string $nome = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Informe o Concurso do bolão.')]
    private ?Concurso $concurso = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $cotaValor = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    private ?Arquivo $planilhaJogosCsv = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    private ?Arquivo $comprovanteJogos = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getConcurso(): ?Concurso
    {
        return $this->concurso;
    }

    public function setConcurso(?Concurso $concurso): static
    {
        $this->concurso = $concurso;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getCotaValor(): ?string
    {
        return $this->cotaValor;
    }

    public function setCotaValor(?string $cotaValor): static
    {
        $this->cotaValor = $cotaValor;

        return $this;
    }

    /**
     * Planilha com os jogos.
     *
     * Um jogo por linha e dezenas separadas por ";".
     *
     * @ Formato CSV.
     */
    public function getPlanilhaJogosCsv(): ?Arquivo
    {
        return $this->planilhaJogosCsv;
    }

    public function setPlanilhaJogosCsv(?Arquivo $planilhaJogosCsv): static
    {
        $this->planilhaJogosCsv = $planilhaJogosCsv;

        return $this;
    }

    public function getComprovanteJogos(): ?Arquivo
    {
        return $this->comprovanteJogos;
    }

    public function setComprovanteJogos(?Arquivo $comprovanteJogos): static
    {
        $this->comprovanteJogos = $comprovanteJogos;

        return $this;
    }
}
