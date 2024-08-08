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

use App\Repository\LoteriaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoteriaRepository::class)]
#[ORM\UniqueConstraint(name: 'nome_UNIQUE', columns: ['nome'])]
#[ORM\UniqueConstraint(name: 'uuid_UNIQUE', columns: ['uuid'])]
#[ORM\UniqueConstraint(name: 'slug_UNIQUE', columns: ['slug_url'])]
#[ORM\HasLifecycleCallbacks]
class Loteria extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank(message: 'Informe o nome da Loteria.')]
    private ?string $nome = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(length: 255)]
    protected ?string $slugUrl = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Informe a URL da API.')]
    private ?string $apiUrl = null;

    /** @var array<int> $aposta */
    #[ORM\Column]
    private array $aposta = [];

    /** @var array<int> $dezenas */
    #[ORM\Column]
    private array $dezenas = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getSlugUrl(): string
    {
        return $this->slugUrl;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(string $apiUrl): static
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getAposta(): array
    {
        return $this->aposta;
    }

    /**
     * @param array<int> $aposta
     */
    public function setAposta(array $aposta): static
    {
        $this->aposta = $aposta;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getDezenas(): array
    {
        return $this->dezenas;
    }

    /**
     * @param array<int> $dezenas
     */
    public function setDezenas(array $dezenas): static
    {
        $this->dezenas = $dezenas;

        return $this;
    }
}
