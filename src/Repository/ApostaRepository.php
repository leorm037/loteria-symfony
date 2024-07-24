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
use App\Entity\Loteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Aposta>
 */
class ApostaRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Aposta::class);
    }

    public function save(Aposta $aposta, bool $flush = false): void {
        $this->getEntityManager()->persist($aposta);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 
     * @param Loteria $loteria
     * @return Aposta[]|null
     */
    public function findNaoConferidasConcursoSorteado(Loteria $loteria): ?array {
        return $this->createQueryBuilder('a')
                        ->select('a,b,c')
                        ->andWhere('c.loteria = :loteria')
                        ->setParameter('loteria', $loteria)
                        ->andWhere('c.dezenas IS NOT NULL')
                        ->andWhere('a.conferida = false')
                        ->innerJoin('a.bolao', 'b', Join::WITH, 'a.bolao = b.id')
                        ->innerJoin('b.concurso', 'c', Join::WITH, 'b.concurso = c.id')
                        ->getQuery()
                        ->getResult()
        ;
    }

    /**
     * @return Aposta[]|null
     */
    public function findApostasByUuidBolao(Uuid $uuid) {
        return $this->createQueryBuilder('a')
                        ->select('a,b')
                        ->where('b.uuid = :uuid')
                        ->setParameter('uuid', $uuid->toBinary())
                        ->innerJoin('a.bolao', 'b', Join::WITH, 'a.bolao = b.id')
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
