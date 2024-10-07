<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Creation;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment', name: 'comment.')]
class CommentController extends AbstractController
{
    #[isGranted('ROLE_USER')]
    #[Route('/{id}', name: 'new', methods: ['POST','GET'])]
    public function new (
        Request $request,
        Creation $creation,
        EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setUser($this->getUser());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreation($creation);
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success','Commentaire ajouter avec succes');
            return $this->redirectToRoute('creations.single', ['id' => $comment->getCreation()->getId()]);

        }
        return $this->render('comment/new.html.twig',[
            'form' => $form,
            'creation' => $creation,
        ]);
    }
}
