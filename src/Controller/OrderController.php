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

/**
 * Controller for handling orders.
 */
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

    /**
     * Handles the order finalization process.
     *
     * Retrieves order items associated with the given order, creates and processes
     * the order form. If the form is submitted and valid, the order data is stored
     * in the session and the user is redirected to the payment page. Otherwise,
     * renders the order page with the form and order details.
     *
     * @param Request $request The HTTP request object.
     * @param Order $order The order entity to be processed.
     * @param EntityManagerInterface $em The Doctrine entity manager for database operations.
     * @param SessionInterface $session The session interface for managing session data.
     *
     * @return Response The HTTP response object.
     */
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

    /**
     *
     */
    #[Route('/user', name: 'user', methods: ['GET'])]
    public function showOrderUser(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('order/userOrders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * Handles the creation or updating of an order based on the user's cart items and request data.
     *
     * This method checks if a pending order exists for the logged-in user. If none exists,
     * a new order is created with the status of 'pending'. If an order exists, its update
     * timestamp is refreshed. The method uses a form to process additional order information.
     *
     * Cart items are iterated over, and for each item, an order item is created and persisted.
     * After processing the cart items, the cart is cleared, and a success flash message is added.
     *
     * The method renders the order template, displaying the order details, order items, and the form.
     *
     * @param Request $request The HTTP request instance.
     * @param EntityManagerInterface $em The entity manager for database operations.
     *
     * @return Response The rendered HTML for the order page.
     */
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
        $this->addFlash('success', 'Commande supprimer');
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

        $this->addFlash('success', 'Article supprimer');

        return $this->redirectToRoute('orders.user');
    }

}