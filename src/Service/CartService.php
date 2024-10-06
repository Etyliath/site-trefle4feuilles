<?php

namespace App\Service;

use App\Entity\Creation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em)
    {
    }

    public  function  getCart():array
    {
        return $this->requestStack->getSession()->get('cart',[]);
    }

    public function addToCart(Creation $creation):void
    {
        $cart = $this->getCart();
        $creationId = $creation->getId();
        if(isset($cart[$creationId])){
            $message = 'deja ajout';
        }else{
            $cart[$creationId] = [
                'creation' => $creation,
                'quantity' => 1,
            ];
        }

        $this->requestStack->getSession()->set('cart', $cart);

    }
    public function removeFromCart(Creation $creation):void
    {
        $cart = $this->getCart();
        if(isset($cart[$creation->getId()])){
            unset($cart[$creation->getId()]);
        }
        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function clearCart():void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    public function releaseReservedCreation()
    {
        $cart = $this->getCart();
        foreach($cart as $item){
            $creation = $this->em->getRepository(Creation::class)->find($item['creation']->getId());
            $creation->setSold(false);
            $this->em->persist($creation);
            $this->em->flush();
        }
        $this->clearCart();
    }
}