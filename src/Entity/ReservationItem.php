<?php

namespace App\Entity;

use App\Repository\ReservationItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationItemRepository::class)]
class ReservationItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    private ?Creation $Creation = null;

    #[ORM\ManyToOne(inversedBy: 'reservationItems')]
    private ?Reservation $Reservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreation(): ?Creation
    {
        return $this->Creation;
    }

    public function setCreation(?Creation $Creation): static
    {
        $this->Creation = $Creation;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->Reservation;
    }

    public function setReservation(?Reservation $Reservation): static
    {
        $this->Reservation = $Reservation;

        return $this;
    }
}
