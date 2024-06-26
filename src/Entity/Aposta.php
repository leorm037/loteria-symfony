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
    private array $dezenas = [];

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected ?Uuid $uuid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Concurso $concurso = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'arquivo_planilha_id', referencedColumnName: 'id', nullable: true)]
    private ?Arquivo $planilha = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'arquivo_comprovante_id', referencedColumnName: 'id', nullable: true)]
    private ?Arquivo $comprovante = null;

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

    public function getConcurso(): ?Concurso
    {
        return $this->concurso;
    }

    public function setConcurso(?Concurso $concurso): static
    {
        $this->concurso = $concurso;

        return $this;
    }

    public function getPlanilha(): ?Arquivo
    {
        return $this->planilha;
    }

    public function setPlanilha(?Arquivo $planilha): static
    {
        $this->planilha = $planilha;

        return $this;
    }

    public function getComprovante(): ?Arquivo
    {
        return $this->comprovante;
    }

    public function setComprovante(?Arquivo $comprovante): static
    {
        $this->comprovante = $comprovante;

        return $this;
    }
}
