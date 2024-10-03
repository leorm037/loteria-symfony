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
    public function list(Usuario $usuario, int $registrosPorPagina = 10, int $paginaAtual = 1, ?string $filter_loteria = null, ?int $filter_concurso = null, ?string $filter_bolao = null, ?bool $filter_apurado = null)
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
        ;

        if ($filter_loteria) {
            $uuid_filter_loteria = Uuid::fromString($filter_loteria);

            $query
                    ->andWhere('l.uuid = :filter_loteria')
                    ->setParameter('filter_loteria', $uuid_filter_loteria->toBinary())
            ;
        }

        if ($filter_concurso) {
            $query
                    ->andWhere('c.numero = :filter_concurso')
                    ->setParameter('filter_concurso', $filter_concurso)
            ;
        }

        if ($filter_bolao) {
            $query
                    ->andWhere('MATCH (b.nome) AGAINST (:filter_bolao IN BOOLEAN MODE) > 0')
                    ->setParameter('filter_bolao', $filter_bolao)
            ;
        }

        if (null !== $filter_apurado) {
            ($filter_apurado) ? $query->andWhere('c.apuracao IS NOT NULL') : $query->andWhere('c.apuracao IS NULL');
        }

        $query
                ->setFirstResult($pagina)
                ->setMaxResults($registros)
        ;

        return new PaginacaoDTO(new Paginator($query), $registrosPorPagina, $paginaAtual);
    }

    /**
     * @return Bolao[]|null
     */
    public function findByBolaoComApostadoresDiferenteDoBolaoAtual(Bolao $bolao)
    {
        return $this->createQueryBuilder('b')
                        ->select('b')
                        ->where('b.usuario = :usuario')
                        ->setParameter('usuario', $bolao->getUsuario())
                        ->andWhere('(Select COUNT(ap.id) From App\Entity\Apostador ap Where ap.bolao = b.id) > 0')
                        ->andWhere('b.id <> :bolao')
                        ->setParameter('bolao', $bolao)
                        ->orderBy('b.nome', 'ASC')
                        ->getQuery()
                        ->getResult()
        ;
    }

    /**
     * @return Bolao[]|null
     */
    public function findByLoteria(Bolao $bolao)
    {
        return $this->createQueryBuilder('b')
                        ->andWhere('b.id <> :id')
                        ->setParameter('id', $bolao->getId())
                        ->orderBy('b.nome', 'ASC')
                        ->getQuery()
                        ->getResult()
        ;
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
