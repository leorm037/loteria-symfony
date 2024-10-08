<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Arquivo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Arquivo>
 */
class ArquivoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Arquivo::class);
    }

    public function save(Arquivo $arquivo, bool $flush = false): void
    {
        $this->getEntityManager()->persist($arquivo);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUuid(Uuid $uuid): ?Arquivo
    {
        return $this->createQueryBuilder('a')
                        ->where('a.uuid = :uuid')
                        ->setParameter('uuid', $uuid->toBinary())
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

    public function delete(Arquivo $arquivo): void
    {
        $this->getEntityManager()->remove($arquivo);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Arquivo[] Returns an array of Arquivo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    //    public function findOneBySomeField($value): ?Arquivo
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
