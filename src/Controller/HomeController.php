<?php

namespace App\Controller;

use App\Repository\CreationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(CreationRepository $creationRepository): Response
    {
        $creations = $creationRepository->findByLastCreations(4);
        return $this->render('home/index.html.twig',[
            'creations' => $creations,
        ]);
    }
}
