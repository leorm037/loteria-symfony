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

use App\Entity\Aposta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Aposta>
 */
class ApostaRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aposta::class);
    }

    public function save(Aposta $aposta, bool $flush = false): void
    {
        $this->getEntityManager()->persist($aposta);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Aposta[]|null
     */
    public function findMinhasApostas()
    {
        return $this->createQueryBuilder('a')
                        ->select('l,c,a')
                        ->innerJoin('a.concurso', 'c', Join::WITH, 'a.concurso = c.id')
                        ->innerJoin('c.loteria', 'l', Join::WITH, 'c.loteria = l.id')
                        ->addOrderBy('l.nome', 'ASC')
                        ->addOrderBy('c.numero', 'DESC')
                        ->getQuery()
                        ->getResult()
        ;
    }

//    /**
//     * @return Aposta[] Returns an array of Aposta objects
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
//    public function findOneBySomeField($value): ?Aposta
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
