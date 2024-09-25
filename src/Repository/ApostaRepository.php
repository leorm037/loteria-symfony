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

use App\DTO\PaginacaoDTO;
use App\Entity\Aposta;
use App\Entity\Bolao;
use App\Entity\Loteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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
    public function findNaoConferidasConcursoSorteado(Loteria $loteria): ?array
    {
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
     * @return PaginacaoDTO|null
     */
    public function findApostasByUuidBolao(Uuid $uuid, int $registrosPorPagina = 10, int $paginaAtual = 1)
    {
        $registros = (!\in_array($registrosPorPagina, [10, 25, 50, 100])) ? 10 : $registrosPorPagina;

        $pagina = ($paginaAtual - 1) * $registrosPorPagina;
        
        $query = $this->createQueryBuilder('a')
                ->select('a,b')
                ->where('b.uuid = :uuid')
                ->setParameter('uuid', $uuid->toBinary())
                ->innerJoin('a.bolao', 'b', Join::WITH, 'a.bolao = b.id')
                ->addOrderBy('a.quantidadeAcertos', 'DESC')
                ->setFirstResult($pagina)
                ->setMaxResults($registros)
        ;

        return new PaginacaoDTO(new Paginator($query), $registrosPorPagina, $paginaAtual);
    }

    /**
     * @return Aposta[]|null
     */
    public function findAllApostasByUuidBolao(Uuid $uuid)
    {
        return $this->createQueryBuilder('a')
                ->select('a,b')
                ->where('b.uuid = :uuid')
                ->setParameter('uuid', $uuid->toBinary())
                ->innerJoin('a.bolao', 'b', Join::WITH, 'a.bolao = b.id')
                ->addOrderBy('a.quantidadeAcertos', 'DESC')
                ->getQuery()
                ->getResult()
        ;
    }

    public function deleteByBolao(Bolao $bolao): void
    {
        $this->createQueryBuilder('a')
                ->delete()
                ->where('a.bolao = :bolao')
                ->setParameter('bolao', $bolao)
                ->getQuery()
                ->execute()
        ;
        $this->getEntityManager()->flush();
    }

    public function delete(Aposta $aposta): void
    {
        $this->getEntityManager()->remove($aposta);
        $this->getEntityManager()->flush();
    }

    public function findByUuid(Uuid $uuid): ?Aposta
    {
        return $this->createQueryBuilder('a')
                        ->where('a.uuid = :uuid')
                        ->setParameter('uuid', $uuid->toBinary())
                        ->getQuery()
                        ->getOneOrNullResult()
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
