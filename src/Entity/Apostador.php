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

use App\Repository\ApostadorRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApostadorRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Apostador extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, nullable: true)]
    #[Assert\NotBlank(message: 'Informe o nome do apostador.')]
    private ?string $nome = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bolao $bolao = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'uuid')]
    protected ?Uuid $uuid = null;

    #[ORM\Column]
    private ?bool $cotaPaga = false;

    #[ORM\Column]
    private int $cotaQuantidade = 1;

    #[ORM\ManyToOne(cascade: ['remove'])]
    private ?Arquivo $comprovantePagamento = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $pix = null;

    public function getBolaoUsuario(): Usuario
    {
        return $this->getBolao()->getUsuario();
    }

    public function getBolaoConcurso(): Concurso
    {
        return $this->getBolao()->getConcurso();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getBolao(): ?Bolao
    {
        return $this->bolao;
    }

    public function setBolao(?Bolao $bolao): static
    {
        $this->bolao = $bolao;

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

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function isCotaPaga(): bool
    {
        if (null !== $this->getComprovantePagamento()) {
            return true;
        }

        return $this->cotaPaga;
    }

    public function setCotaPaga(bool $cotaPaga): static
    {
        $this->cotaPaga = $cotaPaga;

        return $this;
    }

    public function getCotaQuantidade(): int
    {
        return $this->cotaQuantidade;
    }

    public function setCotaQuantidade(int $cotaQuantidade): static
    {
        $this->cotaQuantidade = $cotaQuantidade;

        return $this;
    }

    public function getComprovantePagamento(): ?Arquivo
    {
        return $this->comprovantePagamento;
    }

    public function setComprovantePagamento(?Arquivo $comprovantePagamento): static
    {
        $this->comprovantePagamento = $comprovantePagamento;

        if (!$this->isCotaPaga() && null !== $this->omprovantePagamento) {
            $this->setCotaPaga(true);
        }

        return $this;
    }

    public function getPix(): ?string
    {
        return $this->pix;
    }

    public function setPix(?string $pix): static
    {
        $this->pix = $pix;

        return $this;
    }
}
