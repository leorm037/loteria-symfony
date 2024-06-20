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

use App\Helper\DateTimeHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

abstract class AbstractEntity
{
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt();
        $this->slugUrl();
        $this->uuid();
    }

    public function preUpdate(): void
    {
        $this->updatedAt();
        $this->slugUrl();
    }

    private function createdAt(): void
    {
        if (property_exists(static::class, 'createdAt') && null === $this->createdAt) {
            $this->createdAt = DateTimeHelper::currentDateTimeImmutable();
        }
    }

    private function updatedAt(): void
    {
        if (property_exists(static::class, 'updatedAt') && null === $this->updatedAt) {
            $this->updatedAt = DateTimeHelper::currentDateTime();
        }
    }

    private function slugUrl(): void
    {
        if (property_exists(static::class, 'slugUrl') && null === $this->slugUrl && property_exists(static::class, 'nome')) {
            $slugger = new AsciiSlugger();
            $this->slugUrl = strtolower($slugger->slug($this->getNome()));
        }
    }

    private function uuid(): void
    {
        if (property_exists(static::class, 'uuid') && null === $this->uuid) {
            $this->uuid = Uuid::v7();
        }
    }
}
