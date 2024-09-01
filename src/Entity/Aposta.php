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

use App\Repository\ApostaRepository;
use App\Validator\ArrayValueNotRepeat;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApostaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Aposta extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var array<string>
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Informe as dezenas da aposta.')]
    #[ArrayValueNotRepeat]
    private array $dezenas = [];

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected ?Uuid $uuid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bolao $bolao = null;

    #[ORM\Column(options: ['default' => 0])]
    private bool $conferida = false;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    private ?int $quantidadeAcertos = null;

    #[Assert\IsTrue(message: 'A quantidade de dezenas informada é menor que a definida na Loteria.')]
    public function isQuantidadeDezenasMenor(): bool
    {
        $min = min($this->getBolao()->getConcurso()->getLoteria()->getAposta());

        if (\count($this->getDezenas()) >= $min) {
            return true;
        }

        return false;
    }

    #[Assert\IsTrue(message: 'A quantidade de dezenas informada é maior que a definida na Loteria.')]
    public function isQuantidadeDezenasMaior(): bool
    {
        $max = max($this->getBolao()->getConcurso()->getLoteria()->getAposta());

        if (\count($this->getDezenas()) <= $max) {
            return true;
        }

        return false;
    }

    #[Assert\IsTrue(message: 'Existem dezenas fora do intervalo das dezenas da Loteria.')]
    public function isDezenasForaDoIntervalo(): bool
    {
        $fora = array_diff($this->getDezenas(), $this->getBolao()->getConcurso()->getLoteria()->getDezenas());

        if (0 == \count($fora)) {
            return true;
        }

        return false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array<string>
     */
    public function getDezenas(): array
    {
        return $this->dezenas;
    }

    /**
     * @param array<string> $dezenas
     */
    public function setDezenas(array $dezenas): static
    {
        $this->dezenas = $dezenas;

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

    public function getBolao(): ?Bolao
    {
        return $this->bolao;
    }

    public function setBolao(?Bolao $bolao): static
    {
        $this->bolao = $bolao;

        return $this;
    }

    public function isConferida(): ?bool
    {
        return $this->conferida;
    }

    public function setConferida(bool $conferida): static
    {
        $this->conferida = $conferida;

        return $this;
    }

    public function getQuantidadeAcertos(): ?int
    {
        return $this->quantidadeAcertos;
    }

    public function setQuantidadeAcertos(?int $quantidadeAcertos): static
    {
        $this->quantidadeAcertos = $quantidadeAcertos;

        return $this;
    }
}
