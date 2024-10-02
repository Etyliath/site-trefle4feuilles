<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/dashboard', name: 'admin.dashboard.')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository, Request $request): Response
    {
        $page = $request->query->get('page',1);
        $comments = $commentRepository->paginatedComment($page);
        return $this->render('admin/dashboard/index.html.twig',[
            'comments' => $comments,
        ]);
    }

}