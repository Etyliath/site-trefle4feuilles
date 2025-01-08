<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\OrderRepository;
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
    public function index(CommentRepository $commentRepository, OrderRepository $orderRepository, EntityManagerInterface $em): Response
    {
        $NumberCommentToValidate = count($commentRepository->commentsValidated(false));
        $NumberOrdersNoDelivered = count($orderRepository->ordersNoDelivered("delivered"));
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('admin/dashboard/index.html.twig', [
            'NumberCommentToValidate' => $NumberCommentToValidate,
            'NumberOrdersNoDelivered' => $NumberOrdersNoDelivered,
            'users' => $users,
        ]);
    }

}