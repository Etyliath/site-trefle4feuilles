<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_ADMIN")]
#[Route('/admin/comments', name: 'admin.comments.')]
class CommentController extends AbstractController
{

    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, CommentRepository $commentRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $filterValidated = $request->query->get('filterValidated');
        if($filterValidated === 'all') {
            $filterValidated = null;
        }
        $filterUsernameOrEmail = $request->query->get('filterUsernameOrEmail');
        $comments = $commentRepository->paginatedCommentFiltered($page, $filterValidated, $filterUsernameOrEmail);
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
            'filterValidated' => $filterValidated,
            'filterUsernameOrEmail' => $filterUsernameOrEmail
        ]);
    }
    #[Route('/{id}/validate', name: 'validate' , methods: ['POST'])]
    public  function validated(string $id, EntityManagerInterface $em): Response
    {
        $comment = $em->getRepository(Comment::class)->find($id);
        if ($comment) {
            $comment->setValidated(true);
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Comment validated');
//            return new JsonResponse(['message' => 'Comment validated'], Response::HTTP_OK);
            return $this->redirectToRoute('admin.comments.index');
        }
//        return new JsonResponse(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
        return $this->render('admin/comment/index.html.twig');
    }

    #[Route('/{id}', name: 'delete',requirements: ['id'=> Requirement::DIGITS] , methods: ['DELETE'])]
    public function delete(string $id , EntityManagerInterface $em): Response
    {
        $comment = $em->getRepository(Comment::class)->find($id);
        if ($comment) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Comment deleted successfully');
            return new jsonResponse(['message'=>'Comment delete'], Response::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
    }
}