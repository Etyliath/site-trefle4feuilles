<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_ADMIN")]
#[Route('/admin/dashboard', name: 'admin.orders.items.')]
class OrderItemController extends AbstractController
{
    #[Route('/orders/items/{id}', name: 'index', methods: ['GET'])]
    public function index(Order $order, EntityManagerInterface $em): Response
    {
        $orderItems = $em->getRepository(OrderItem::class)->findby(['ordering' => $order]);
        return $this->render('admin/order/orderItem.html.twig', [
            'orderItems' => $orderItems,
        ]);
    }

}