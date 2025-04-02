<?php

namespace App\Service;

use App\Entity\Creation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack           $requestStack,
        private EntityManagerInterface $em)
    {
    }

    public function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    public function addToCart(Creation $creation): void
    {
        $cart = $this->getCart();
        if (in_array($creation, $cart)) {
            $message = 'deja ajout';
        } else {
            $cart[] = $creation;
        }
        $this->requestStack->getSession()->set('cart', $cart);

    }

    public function getTotal(): array
    {
        $cart = $this->requestStack->getSession()->get('cart');
        $totalCart = [];
        if ($cart) {
            foreach ($cart as $id => $creation) {
                $totalCart[] = [
                    'id' => $id,
                    'creation' => $creation,
                ];
            }
        }
        return $totalCart;
    }

    public function removeFromCart(Creation $creation): void
    {
        $cart = $this->getCart();
        foreach ($cart as $key => $item) {
            if ($item->getId() === $creation->getId()) {
                unset($cart[$key]);
                $cart = array_values($cart);
                break;
            }
        }
        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function clearCart(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    public function releaseReservedCreation(): void
    {
        $cart = $this->getCart();
        foreach ($cart as $item) {
            $creation = $this->em->getRepository(Creation::class)->findOneBy(['id' => $item->getId()]);
            $creation->setSold(false);
            $this->em->persist($creation);
            $this->em->flush();
        }
        $this->clearCart();
    }
}