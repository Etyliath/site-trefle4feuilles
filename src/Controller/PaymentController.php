<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\StripeCheckoutSession;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller handling payment processes including payment initiation, webhook processing,
 * and success or cancellation pages.
 */
#[Route('/payment', name: 'payment.')]
class PaymentController extends AbstractController
{
    /**
     * Handles the payment process by retrieving the current user's pending order
     * and initiating a Stripe Checkout session using the Stripe API.
     *
     * Retrieves order data from the session, fetches the user's pending order
     * from the database, and creates a payment session with the necessary details.
     * Redirects the user to the Stripe Checkout URL to complete the transaction.
     *
     * @param OrderRepository $orderRepository Repository to fetch order data.
     * @param SessionInterface $session Session to retrieve stored order data.
     * @return RedirectResponse Redirects to the Stripe Checkout URL for payment.
     */
    #[Route('/', name: 'pay')]
    public function payment(
        OrderRepository $orderRepository,
        SessionInterface $session): RedirectResponse
    {
        $data = $session->get('order_data',[]);
        $user = $this->getUser();
        $order = $orderRepository->findByUserAndStatus($user, 'pending');

        $payment = new StripeCheckoutSession($_ENV['STRIPE_SECRET_KEY']);
        $cart = [];
        $orderId = $order->getId();
        foreach ($order->getOrderItems() as $orderItem) {
            $cart [] = $orderItem->getCreation();
        }
        $stripeCheckout = $payment->startPayment($cart, $user, $orderId, $data);
        $stripeCheckoutUrl = $stripeCheckout->url;

        return $this->redirect($stripeCheckoutUrl);
    }

    /**
     * Handles the Stripe webhook events and processes relevant actions based on event type.
     *
     * Retrieves the payload and signature from the request to validate the webhook event
     * using Stripe's API. If the event is successfully validated and is of type
     * 'checkout.session.completed', it updates the relevant order in the database with
     * payment details and status.
     *
     * @param Request $request The incoming HTTP request containing the webhook payload.
     * @param EntityManagerInterface $em Doctrine entity manager for interacting with the database.
     * @return Response Responds with the appropriate HTTP status after handling the webhook.
     */
    #[Route('/webhook', name: 'webhook')]
    public function handle(Request $request, EntityManagerInterface $em): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');
        $endPointSecretKey = $_ENV['STRIPE_WEBHOOK_SECRET'];

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $endPointSecretKey
            );

        } catch (\UnexpectedValueException $e) {
            //invalid payload
            return new Response('invalid payload', 400);

        } catch (SignatureVerificationException $e) {
            return new Response('invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $data = $event->data->object;
            $metadata = $event->data->object->metadata;
            dump($metadata);
            $order = $em->getRepository(Order::class)->find(['id' => $metadata['order_id']]);
            $order->setPayment($data['id']);
            $order->setStatus('paid');
            $order->setInstruction($data['instruction']);
            $em->persist($order);
            $em->flush();

        }

        return new Response('webhook received', 200);

    }

    #[Route('/success/', name: 'success')]
    public function success(): Response
    {

        return $this->render('payment/success.html.twig');
    }

    #[Route('/cancel', name: 'cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig', []);

    }

}