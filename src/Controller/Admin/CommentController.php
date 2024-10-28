<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_ADMIN")]
#[Route('/admin/comments', name: 'admin.comments.')]
class CommentController extends AbstractController
{

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findAll();
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
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
            return new JsonResponse(['message' => 'Comment validated'], Response::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
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