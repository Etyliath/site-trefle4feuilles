<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByUserAndStatus($user, $status): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginatedOrderFilters(int $page, int|null $order, string|null $name, string|null $status): PaginationInterface
    {
        $qb = $this->createQueryBuilder('o');

        if ($order) {
            $qb->andWhere('o.id = :order')
                ->setParameter('order', $order);
        }

        if($status) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        $qb->orderBy('o.createdAt', 'DESC');
        $qb->leftJoin('o.user', 'u');
        if ($name) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.username', ':name'),
                $qb->expr()->like('u.email', ':name')
            ))
                ->setParameter('name', '%' . $name . '%');
        }

        $qb->getQuery();

        return $this->paginator->paginate(
            $qb,
            $page,
            5,
            [
                'distinct' => true,
                'sortfieldAllowList' => [
                    'o.createdAt',
                    'u.username',
                    'u.email',
                ]
            ]);

    }

    public function ordersNoDelivered(string|null $status): Array
    {
        $qb = $this->createQueryBuilder('o');
            if($status !== null){
                $qb->andWhere(
                    $qb->expr()->neq('o.status', ':status'))
                    ->setParameter('status', $status);
            }
            return $qb->getQuery()->getResult();
    }

}
