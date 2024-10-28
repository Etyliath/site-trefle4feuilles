<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[isGranted("ROLE_USER")]
#[Route('/orders', name: 'orders.')]
class OrderController extends AbstractController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    #[Route('/', name: 'index')]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/show/{id}', name: 'show', methods: ['GET', 'POST'])]
    public function show(Order $order, EntityManagerInterface $em): Response
    {
        $orderItems = $em->getRepository(OrderItem::class)->findOrderItemByOrderId($order->getId());

        return $this->render('order/show.html.twig', [
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    #[Route('/finalise/{id}', name: 'finalise', methods: ['GET', 'POST'])]
    public function finalise(Request $request, Order $order, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $orderItems = $em->getRepository(OrderItem::class)->findOrderItemByOrderId($order->getId());
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['order'];
            $session->set('order_data', $data);

            return $this->redirectToRoute('payment.pay');
        }

        return $this->render('order/index.html.twig', [
            'order' => $order,
            'orderItems' => $orderItems,
            'form' => $form,
        ]);
    }

    #[Route('/user', name: 'user', methods: ['GET'])]
    public function showOrderUser(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('order/userOrders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $cart = $this->cartService->getCart();
        $user = $this->getUser();
        $message = '';
        $order = $em->getRepository(Order::class)->findByUserAndStatus($user, 'pending');
        if ($order === null) {
            $order = new Order();
            $order->setUser($user);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUpdatedAt(new \DateTimeImmutable());
            $order->setStatus('pending');
            $em->persist($order);
            $em->flush();
            $message = 'Order created';
        }else{
            $order->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($order);
            $em->flush();
            $message = 'Order update';
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($order);
            $em->flush();
        }

        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $creation = $em->getRepository(Creation::class)->find($item->getId());
            $orderItem->setCreation($creation);
            $orderItem->setOrdering($order);
            $em->persist($orderItem);
            $em->flush();

        }
        $this->cartService->clearCart();
        $this->addFlash('success', $message);

        $orderItems = $em->getRepository(OrderItem::class)->findOrderItemByOrderId($order->getId());

        return $this->render('order/index.html.twig', [
            'order' => $order,
            'orderItems' => $orderItems,
            'form' => $form,
        ]);

    }

    #[Route('/{id}', name: 'remove', methods: ['GET', 'POST'])]
    public function remove(Order $order, EntityManagerInterface $em): Response
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $creation = $em->getRepository(Creation::class)->find($orderItem->getCreation()->getId());
            $creation->setSold(false);
            $em->persist($creation);
            $em->flush();
            $em->remove($orderItem);
            $em->flush();
        }

        $em->remove($order);
        $em->flush();
        $this->addFlash('success', 'Order removed');
        return $this->redirectToRoute('orders.user');
    }

    #[Route('/orderItem/{id}', name: 'remove.orderItem', methods: ['GET', 'POST'])]
    public function removeItem(Request $request, OrderItem $orderItem, EntityManagerInterface $em): Response
    {
        $orderItemId = $orderItem->getId();
        $creation = $em->getRepository(Creation::class)->find($orderItem->getCreation()->getId());
        $creation->setSold(false);
        $em->persist($creation);
        $em->flush();
        $em->remove($orderItem);
        $em->flush();
        $order = $em->getRepository(Order::class)->find($orderItem->getOrdering()->getId());
        $order->setUpdatedAt(new \DateTimeImmutable());
        $em->persist($order);
        $em->flush();

        if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('order/deleteOrderItem.html.twig', [
                'orderItemId' => $orderItemId,
            ]);
        }

        $this->addFlash('success', 'Item removed');

        return $this->redirectToRoute('orders.user');
    }

}