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
            ->where('c.sold= false')
            ->orderBy('c.createdAt', 'DESC')
            ->leftJoin('c.category', 'cy')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function pagination(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
            ->select('c', 'cy')
            ->orderBy('c.createdAt', 'DESC')
            ->leftJoin('c.category', 'cy')
            ->getQuery(),
            $page,
            6
        );
    }

    /**
     * Retrieves a paginated collection of creations filtered by optional name and category criteria.
     *
     * @param int $page The current page number for pagination.
     * @param string|null $name An optional filter for creation name (supports partial match).
     * @param string|null $category An optional filter for the category of creations.
     *
     * @return PaginationInterface Returns a paginated result set of creations based on the applied filters.
     */
    public function paginatedCreationsByFilters(int $page, string|null $name, string|null $category): PaginationInterface
    {
        $qb = $this->createQueryBuilder('c');

        if ($category) {
            $qb->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }
        if ($name) {
            $qb->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
        $qb->getQuery();

        return $this->paginator->paginate(
            $qb,
            $page,
            6
        );
    }
}
