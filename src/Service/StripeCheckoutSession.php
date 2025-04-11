<?php

namespace App\Service;

use App\Entity\User;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

/**
 * Handles the creation and management of Stripe Checkout sessions.
 */
class StripeCheckoutSession
{

    public function __construct( private $apiSecretKey)
    {
        Stripe::setApiKey($this->apiSecretKey);
        Stripe::setApiVersion('2024-09-30.acacia');
    }

    /**
     * Initiates a payment session using the provided order and user data.
     *
     * @param array $data The data related to the order items, including pricing and names.
     * @param User|null $user The user initiating the payment, null if not authenticated.
     * @param int $orderId The identifier of the order being processed.
     * @param array $delivery The delivery details including address, city, and postal code.
     *
     * @return Session Returns the created payment session.
     * @throws ApiErrorException
     */
    public function startPayment(array $data, ?User $user, int $orderId, array $delivery): Session
    {
        return Session::create([
            'customer_email' => $user->getEmail(),
            'line_items' => [
                array_map(fn($creation) => [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name'=>$creation->getName()
                        ],
                        'unit_amount' => $creation->getPrice(),
                    ]
                ], $data)
            ],
            'mode' => 'payment',
            'success_url' => $_ENV['SUCCESS_URL'],
            'cancel_url' => $_ENV['CANCEL_URL'],
//            'billing_address_collection' => 'required',
//            'shipping_address_collection' => [
//                'allowed_countries' => ['FR'],
//            ],
            'metadata' => [
                'order_id' => $orderId,
                'customer_id' => $user->getId(),
                'customer_name' => $user->getFirstname() . ' ' . $user->getLastname(),
                'customer_address' =>$delivery['address'],
                'customer_zipcode' => $delivery['zipcode'],
                'customer_city' => $delivery['city'],

            ],
        ]);
    }

}