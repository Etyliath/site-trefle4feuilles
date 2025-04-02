<?php

namespace App\Repository;

use App\Entity\ReservationItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationItem>
 */
class ReservationItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationItem::class);
    }

    public function findReservationItemByReservationId(int $reservationId): array
    {
        return $this->createQueryBuilder('ri')
            ->andWhere('ri.Reservation = :reservationId')
            ->setParameter('reservationId', $reservationId)
            ->getQuery()
            ->getResult();
    }
}
