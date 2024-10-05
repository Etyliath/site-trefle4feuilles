<?php

namespace App\Controller;

use App\Entity\Creation;
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
//        $cart = $session->get('cart');
        $cart = $this->cartService->getCart();
        return $this->render('cart/index.html.twig',[
            'cart' => $cart
        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['GET', 'POST'])]
    public  function add(SessionInterface $session, Creation $creation, EntityManagerInterface $em): Response
    {
//        $card = $session->get('cart',[]);
        $cart = $this->cartService->getCart();
        $id = $creation->getId();
        if(!isset($card[$id])){
            $this->cartService->addToCart($creation);
            $creation->setSold(true);
            $em->persist($creation);
            $em->flush();
        }else{
            $this->addFlash('danger', 'la création est déjà dans le panier');
        }
        return $this->redirectToRoute('cart.index');
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['GET', 'POST'])]
    public function remove(SessionInterface $session, Creation $creation, EntityManagerInterface $em): Response
    {
        $creation->setSold(false);
        $em->persist($creation);
        $em->flush();
        $this->cartService->removeFromCart($creation);
        return $this->redirectToRoute('cart.index');
    }
}
