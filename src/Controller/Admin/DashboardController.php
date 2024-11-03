<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Creation;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_ADMIN")]
#[Route('/admin/dashboard', name: 'admin.dashboard.')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {
        $page = $request->query->get('page', 1);
        $comments = $commentRepository->paginatedCommentNotValidate($page);
        $filterCategory = $request->query->get('category');
        $filterName = $request->query->get('name');
        $creations = $em->getRepository(Creation::class)->paginatedCreationsByFilters($page, $filterName, $filterCategory);
        $categories = $em->getRepository(Category::class)->findAll();
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('admin/dashboard/index.html.twig', [
            'comments' => $comments,
            'creations' => $creations,
            'users' => $users,
            'categories' => $categories,
            'category' => $filterCategory,
            'name' => $filterName,
        ]);
    }

}