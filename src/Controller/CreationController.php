<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Repository\CategoryRepository;
use App\Repository\CreationRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/creations', name: 'creations.')]
class CreationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        CreationRepository $creationRepository,
        Request $request, CategoryRepository $categoryRepository): Response
    {
//
        $page = $request->query->getInt('page', 1);
        $creations = $creationRepository->paginatedCreations($page, $request->query->get('filter'));

        $filter = (int)$request->query->get('filter');
        $titleCategory = $categoryRepository->find($filter);
//       dd($titleCategory);
        $categories = $categoryRepository->findAll();
        return $this->render('creation/index.html.twig', [
            'creations' => $creations,
            'categories' => $categories,
            'titleCategory' => $titleCategory,
        ]);
    }
    #[Route('/{id}', name: 'single', methods: ['GET'])]
    public function single(Creation $creation): Response
    {
        return $this->render('creation/single.html.twig', [
            'creation' => $creation,
        ]);
    }
}
