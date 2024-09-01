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
use App\Entity\Concurso;
use App\Entity\Loteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Concurso>
 */
class ConcursoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Concurso::class);
    }

    public function findByLoteriaAndNumero(Loteria $loteria, int $numero): ?Concurso
    {
        return $this->createQueryBuilder('c')
                        ->select('c,l')
                        ->andWhere('c.loteria = :loteria')
                        ->setParameter('loteria', $loteria)
                        ->andWhere('c.numero = :numero')
                        ->setParameter('numero', $numero)
                        ->innerJoin('c.loteria', 'l', Join::WITH, 'c.loteria = l.id')
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

    /**
     * @return PaginacaoDTO<Concurso>|null
     */
    public function findByLoteria(Loteria $loteria, int $registrosPorPagina = 10, int $paginaAtual = 1)
    {
        $registros = (!\in_array($registrosPorPagina, [10, 25, 50, 100])) ? 10 : $registrosPorPagina;
        $pagina = ($paginaAtual - 1) * $registrosPorPagina;

        $query = $this->createQueryBuilder('c')
                ->andWhere('c.loteria = :loteria')
                ->setParameter('loteria', $loteria)
                ->addOrderBy('c.numero', 'DESC')
                ->setFirstResult($pagina)
                ->setMaxResults($registros)
        ;

        return new PaginacaoDTO(new Paginator($query), $registrosPorPagina, $paginaAtual);
    }

    public function save(Concurso $concurso, bool $flush = false): void
    {
        $this->getEntityManager()->persist($concurso);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Concurso[] Returns an array of Concurso objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    //    public function findOneBySomeField($value): ?Concurso
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
