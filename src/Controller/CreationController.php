<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\CreationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for handling creation-related routes and functionalities.
 */
#[Route('/creations', name: 'creations.')]
class CreationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        CreationRepository $creationRepository,
        Request $request, CategoryRepository $categoryRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $filterCategory = $request->query->get('category');
        $filterName = $request->query->get('name');
        $creations = $creationRepository->paginatedCreationsByFilters($page,  $filterName, $filterCategory);
        $filter = (int)$filterCategory;
        $titleCategory = $categoryRepository->find($filter);
        $categories = $categoryRepository->findAll();
        return $this->render('creation/index.html.twig', [
            'creations' => $creations,
            'categories' => $categories,
            'titleCategory' => $titleCategory,
            'category' => $filterCategory,
            'name' => $filterName,
        ]);
    }

    #[Route('/{id}', name: 'single', methods: ['GET'])]
    public function single(Creation $creation, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(['creation' => $creation, 'validated' => true]);
//        dd($comments);
        return $this->render('creation/single.html.twig', [
            'creation' => $creation,
            'comments' => $comments,
        ]);
    }
}
