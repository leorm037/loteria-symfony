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
use App\Entity\Apostador;
use App\Entity\Bolao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Apostador>
 */
class ApostadorRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Apostador::class);
    }

    public function save(Apostador $apostador, bool $flush = false): void
    {
        $this->getEntityManager()->persist($apostador);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Apostador $apostador): void
    {
        $this->getEntityManager()->remove($apostador);
        $this->getEntityManager()->flush();
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

    /**
     * @return PaginacaoDTO|null
     */
    public function findByBolao(Bolao $bolao, int $registrosPorPagina = 10, int $paginaAtual = 1)
    {
        $registros = (!\in_array($registrosPorPagina, [10, 25, 50, 100])) ? 10 : $registrosPorPagina;

        $pagina = ($paginaAtual - 1) * $registrosPorPagina;

        $query = $this->createQueryBuilder('a')
                ->where('a.bolao = :bolao')
                ->setParameter('bolao', $bolao)
                ->orderBy('a.nome', 'ASC')
                ->setFirstResult($pagina)
                ->setMaxResults($registros)
        ;

        return new PaginacaoDTO(new Paginator($query), $registrosPorPagina, $paginaAtual);
    }

    /**
     * 
     * @param Bolao $bolao
     * @return Apostador[]|null
     */
    public function findByBolaoParaSelecionarParaImportar(Bolao $bolao)
    {
        return $this->createQueryBuilder('a')
                        ->where('a.bolao = :bolao')
                        ->setParameter('bolao', $bolao)
                        ->orderBy('a.nome', 'ASC')
                        ->getQuery()
                        ->getResult()
        ;
    }

    public function findByUuid(Uuid $uuid): ?Apostador
    {
        return $this->createQueryBuilder('a')
                        ->where('a.uuid = :uuid')
                        ->setParameter('uuid', $uuid->toBinary())
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return Apostador[] Returns an array of Apostador objects
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
    //    public function findOneBySomeField($value): ?Apostador
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
