<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly PaginatorInterface $paginator)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByUserAndStatus($user, $status): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.User = :user')
            ->andWhere('r.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retrieves a paginated list of reservation filters based on the given parameters.
     *
     * @param int $page The current page number for pagination.
     * @param int|null $reservation The specific reservation ID to filter by, or null for no filtering.
     * @param string|null $name The name or email of the user to filter by, or null for no filtering.
     * @param string|null $status The status of the reservation to filter by, or null for no filtering.
     *
     * @return PaginationInterface|null A paginated collection of reservation records, or null if no results are found.
     */
    public function paginatedReservationFilters(int $page, int|null $reservation, string|null $name, string|null $status): ?PaginationInterface
    {
        $qb = $this->createQueryBuilder('r');
        if ($reservation) {
            $qb->andWhere('r.id = :reservation')
                ->setParameter('reservation', $reservation);
        }
        if ($status) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }
        $qb->orderBy('r.createdAt', 'DESC');
        $qb->leftJoin('r.User', 'u');
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
                    'r.createdAt',
                    'u.username',
                    'u.email',
                ]
            ]);
    }

    public function reservationNoDelivered(string|null $status): array
    {
        $qb = $this->createQueryBuilder('r');
        if ($status!==null) {
            $qb->andWhere(
                $qb->expr()->neq('r.status', ':status'))
                    ->setParameter('status', $status);
        }
        return $qb->getQuery()->getResult();
    }

}
