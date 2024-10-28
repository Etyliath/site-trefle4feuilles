<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    private ?Creation $creation = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    private ?Order $ordering = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreation(): ?Creation
    {
        return $this->creation;
    }

    public function setCreation(?Creation $creation): static
    {
        $this->creation = $creation;

        return $this;
    }

    public function getOrdering(): ?Order
    {
        return $this->ordering;
    }

    public function setOrdering(?Order $ordering): static
    {
        $this->ordering = $ordering;

        return $this;
    }
}
