<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Entity\Order;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_USER")]
#[Route('/cart', name: 'cart.')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService)
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(SessionInterface $session, EntityManagerInterface $em): Response
    {
        $cart = $this->cartService->getTotal();
        $order = $em->getRepository(Order::class)->findByUserAndStatus( $this->getUser(), 'pending');
        return $this->render('cart/index.html.twig',[
            'cart' => $cart,
            'order' => $order,
        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['GET'])]
    public  function add(Creation $creation, EntityManagerInterface $em): Response
    {
        $cart = $this->cartService->getCart();
        $id = $creation->getId();
        if(!isset($cart[$id])){
            $this->cartService->addToCart($creation);
            $creation->setSold(true);
            $em->persist($creation);
            $em->flush();
        }else{
            $this->addFlash('danger', 'la création est déjà dans le panier');
        }
        return $this->redirectToRoute('creations.index');
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['GET', 'POST'])]
    public function remove(Creation $creation, EntityManagerInterface $em): Response
    {
        $creation->setSold(false);
        $em->persist($creation);
        $em->flush();
        $this->cartService->removeFromCart($creation);
        return $this->redirectToRoute('cart.index');
    }

    #[Route('/clear', name: 'clear', methods: ['GET'])]
    public function clearCart(): Response
    {
        $this->cartService->releaseReservedCreation();
        return $this->redirectToRoute('cart.index');

    }
}
