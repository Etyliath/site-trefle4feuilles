<?php

namespace App\EventListener;

use App\Service\CartService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class CartListener
{
    public function __construct(private  CartService $cartService)
    {
    }

    #[AsEventListener(event: LogoutEvent::class, priority: 128)]
    public function onLogoutEvent(LogoutEvent $event): void
    {
        $this->cartService->releaseReservedCreation();
    }
}
