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

use App\Entity\Bolao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Bolao>
 */
class BolaoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bolao::class);
    }

    public function save(Bolao $bolao, bool $flush = false): void
    {
        $this->getEntityManager()->persist($bolao);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUuid(Uuid $uuid): ?Bolao
    {
        return $this->createQueryBuilder('b')
                        ->select('b,c,l')
                        ->where('b.uuid = :uuid')
                        ->setParameter('uuid', $uuid->toBinary())
                        ->innerJoin('b.concurso', 'c', Join::WITH, 'b.concurso = c.id')
                        ->innerJoin('c.loteria', 'l', Join::WITH, 'c.loteria = l.id')
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

    public function list()
    {
        return $this->createQueryBuilder('b')
                        ->select('b,c,l')
                        ->innerJoin('b.concurso', 'c', Join::WITH, 'b.concurso = c.id')
                        ->innerJoin('c.loteria', 'l', Join::WITH, 'c.loteria = l.id')
                        ->addOrderBy('l.nome', 'ASC')
                        ->addOrderBy('c.numero', 'DESC')
                        ->addOrderBy('b.nome', 'ASC')
                        ->getQuery()
                        ->getResult()
        ;
    }

    //    /**
    //     * @return Bolao[] Returns an array of Bolao objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    //    public function findOneBySomeField($value): ?Bolao
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
