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

use App\Repository\ConcursoRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConcursoRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'write_rare_cache')]
#[ORM\HasLifecycleCallbacks]
class Concurso extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Informe o número do Concurso.')]
    #[Assert\Positive]
    private ?int $numero = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $apuracao = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $local = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $municipio = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $uf = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected ?Uuid $uuid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Loteria $loteria = null;

    /**
     * @var array<int,array{descricaoFaixa: string, faixa: int,numeroDeGanhadores: int, valorPremio: int}>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $rateioPremio = null;

    /**
     * @var array<int>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $dezenas = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getApuracao(): ?DateTimeInterface
    {
        return $this->apuracao;
    }

    public function setApuracao(?DateTimeInterface $apuracao): static
    {
        $this->apuracao = $apuracao;

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(string $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function getMunicipio(): ?string
    {
        return $this->municipio;
    }

    public function setMunicipio(?string $municipio): static
    {
        $this->municipio = $municipio;

        return $this;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function setUf(?string $uf): static
    {
        $this->uf = $uf;

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

    public function getLoteria(): ?Loteria
    {
        return $this->loteria;
    }

    public function setLoteria(?Loteria $loteria): static
    {
        $this->loteria = $loteria;

        return $this;
    }

    /**
     * @return array<int,array{descricaoFaixa: string, faixa: int,numeroDeGanhadores: int, valorPremio: int}>|null
     */
    public function getRateioPremio(): ?array
    {
        return $this->rateioPremio;
    }

    /**
     * @param array<int,array{descricaoFaixa: string, faixa: int,numeroDeGanhadores: int, valorPremio: int}>|null $rateioPremio
     */
    public function setRateioPremio(?array $rateioPremio): static
    {
        $this->rateioPremio = $rateioPremio;

        return $this;
    }

    /**
     * @return array<int>|null
     */
    public function getDezenas(): ?array
    {
        return $this->dezenas;
    }

    /**
     * @param array<int>|null $dezenas
     */
    public function setDezenas(?array $dezenas): static
    {
        $this->dezenas = $dezenas;

        return $this;
    }
}
