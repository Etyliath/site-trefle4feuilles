<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/profile', name: 'admin.profile.')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'users', methods: ['GET'])]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/profile/user.html.twig', [
            'users' => $users,
        ]);
    }

}