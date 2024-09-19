<?php

namespace App\Controller\Admin;

use App\Entity\Creation;
use App\Form\CreationType;
use App\Repository\CreationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/admin/creations', name: 'admin.creations.')]
class CreationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CreationRepository $repository): Response
    {
        $creations = $repository->findAll();
        return $this->render('admin/creation/index.html.twig',[
            'creations' => $creations,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST','GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $creation = new Creation();
        $form = $this->createForm(CreationType::class, $creation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($creation);
            $entityManager->flush();
            $this->addFlash('success','la création a été crée');
            return $this->redirectToRoute('admin.creations.index');
        }
        return $this->render('admin/creation/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'edit', requirements: ['id'=> Requirement::DIGITS], methods: ['GET','POST'])]
    public function edit(Request $request, Creation $creation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreationType::class, $creation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success','la modification a été effectuée');
            return $this->redirectToRoute('admin.creations.index');
        }
        return $this->render('admin/creation/edit.html.twig', [
            'form' => $form,
            'creation' => $creation,
        ]);
    }

    #[Route('/{id}', name: 'delete',requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
    public function delete( Creation $creation, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($creation);
        $entityManager->flush();
        return $this->redirectToRoute('admin.creations.index');
    }
}
