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

use App\Entity\Loteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Loteria>
 */
class LoteriaRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loteria::class);
    }

    /**
     * 
     * @param Loteria $loteria
     * @param bool $flush
     * @return void
     */
    public function save(Loteria $loteria, bool $flush = false): void
    {
        $this->getEntityManager()->persist($loteria);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 
     * @return Loteria[]|null
     */
    public function getAll(): ?array
    {
        return $this->createQueryBuilder('l')
                        ->orderBy('l.nome', 'ASC')
                        ->getQuery()
                        ->getResult()
        ;
    }

    /**
     * 
     * @param Uuid $uuid
     * @return Loteria|null
     */
    public function findByUuid(Uuid $uuid): ?Loteria
    {
        return $this->createQueryBuilder('l')
                        ->where('l.uuid = :uuid')
                        ->setParameter('uuid', $uuid->toBinary())
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return Loteria[] Returns an array of Loteria objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    //    public function findOneBySomeField($value): ?Loteria
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
