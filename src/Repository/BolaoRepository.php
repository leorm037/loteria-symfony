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
use App\Entity\Bolao;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @return PaginacaoDTO|null
     */
    public function list(Usuario $usuario, int $registrosPorPagina = 10, int $paginaAtual = 1)
    {
        $registros = (!\in_array($registrosPorPagina, [10, 25, 50, 100])) ? 10 : $registrosPorPagina;

        $pagina = ($paginaAtual - 1) * $registrosPorPagina;

        $query = $this->createQueryBuilder('b')
                ->select('b,c,l')
                ->addSelect('(Select COUNT(a.id) From App\Entity\Aposta a Where a.bolao = b.id) As apostas')
                ->addSelect('(Select COUNT(ap.id) From App\Entity\Apostador ap Where ap.bolao = b.id) As apostadores')
                ->addSelect('(Select MAX(a2.quantidadeAcertos) From App\Entity\Aposta a2 Where a2.bolao = b.id) As apostasMax')
                ->where('b.usuario = :usuario')
                ->setParameter('usuario', $usuario)
                ->innerJoin('b.concurso', 'c', Join::WITH, 'b.concurso = c.id')
                ->innerJoin('c.loteria', 'l', Join::WITH, 'c.loteria = l.id')
                ->addOrderBy('l.nome', 'ASC')
                ->addOrderBy('c.numero', 'DESC')
                ->addOrderBy('b.nome', 'ASC')
                ->setFirstResult($pagina)
                ->setMaxResults($registros)
        ;

        return new PaginacaoDTO(new Paginator($query), $registrosPorPagina, $paginaAtual);
    }

    public function delete(Bolao $bolao): void
    {
        $this->getEntityManager()->remove($bolao);
        $this->getEntityManager()->flush();
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
