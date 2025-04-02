<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\OrderRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_ADMIN")]
#[Route('/admin/dashboard', name: 'admin.dashboard.')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        CommentRepository $commentRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $em,
        ReservationRepository $reservationRepository,
    ): Response
    {
        $numberCommentToValidate = count($commentRepository->commentsValidated(false));
        $numberOrdersNoDelivered = count($orderRepository->ordersNoDelivered("delivered"));
        $numberReservationNoDelivered = count($reservationRepository->reservationNoDelivered("delivered"));
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('admin/dashboard/index.html.twig', [
            'numberCommentToValidate' => $numberCommentToValidate,
            'numberOrdersNoDelivered' => $numberOrdersNoDelivered,
            'numberReservationNoDelivered' => $numberReservationNoDelivered,
            'users' => $users,
        ]);
    }

}