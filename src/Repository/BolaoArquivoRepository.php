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
use App\Entity\BolaoArquivo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BolaoArquivo>
 */
class BolaoArquivoRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, BolaoArquivo::class);
    }

    public function save(BolaoArquivo $bolaoArquivo, bool $flush = false): void {
        $this->getEntityManager()->persist($bolaoArquivo);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 
     * @param Bolao $bolao
     * @return BolaoArquivo[]|null
     */
    public function findByBolao(Bolao $bolao) {
        return $this->createQueryBuilder('ba')
                        ->select('ba,b,a')
                        ->where('b.uuid = :uuid')
                        ->setParameter('uuid', $bolao->getUuid()->toBinary())
                        ->innerJoin('ba.bolao', 'b', Join::WITH, 'ba.bolao = b.id')
                        ->innerJoin('ba.arquivo', 'a', Join::WITH, 'ba.arquivo = a.id')
                        ->getQuery()
                        ->getResult()
        ;
    }

    public function delete(BolaoArquivo $bolaoArquivo): void {
        $this->getEntityManager()->remove($bolaoArquivo);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return BolaoArquivo[] Returns an array of BolaoArquivo objects
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
    //    public function findOneBySomeField($value): ?BolaoArquivo
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
