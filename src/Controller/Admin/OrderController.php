<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller responsible for managing orders in the admin panel.
 */
#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/orders', name: 'admin.orders.')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $filterOrder = (int)$request->query->get('filterOrder');
        $filterUsernameOrEmail = $request->query->get('filterUsernameOrEmail');
        $filterStatus = $request->query->get('filterStatus');
        if($filterStatus === 'all'){
            $filterStatus = null;
        }
        $orders = $orderRepository->paginatedOrderFilters($page, $filterOrder, $filterUsernameOrEmail, $filterStatus);
        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'filterOrder' => $filterOrder,
            'filterUsernameOrEmail' => $filterUsernameOrEmail,
            'filterStatus' => $filterStatus
        ]);
    }

    #[route('/{id}', name: 'delivered', methods: ['GET', 'POST'])]
    public function delivered(Order $order, EntityManagerInterface $em): Response
    {
        $order->setStatus('delivered');
        $em->persist($order);
        $em->flush();
        return $this->redirectToRoute('admin.orders.index');

    }
}