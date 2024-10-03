<?php

namespace App\Repository;

use App\Entity\Creation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Creation>
 */
class CreationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Creation::class);
    }

    public function findByLastCreations(int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cy')
            ->orderBy('c.createdAt', 'DESC')
            ->leftJoin('c.category', 'cy')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function paginatedCreations(int $page,string|null $filter): PaginationInterface
    {
        if(!$filter){
            return $this->paginator->paginate(
                $this->createQueryBuilder('c'),
                $page,
                6
            );
        }else{
            return $this->paginator->paginate(
                $this->createQueryBuilder('c')
                ->andWhere('c.category = :filter')
                ->setParameter('filter', $filter),
                $page,
                6
            );
        }

    }
}
