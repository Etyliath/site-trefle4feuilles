<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil', name: 'profil.')]
class ProfilController extends AbstractController
{

    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function editProfil(
        Request                     $request,
        EntityManagerInterface      $manager,
        UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfilUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success','Profil modifé avec success');
            return $this->redirectToRoute('home');
        }
        return $this->render('profil/editProfil.html.twig', [
            'form' => $form,
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/listUsers', name: 'listUsers', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository)
    {
        $users = $userRepository->findAll();
        return $this->render('admin/profil/listUsers.html.twig', [
            'users' => $users,
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/setAdmin/{id}', name: 'setAdmin', methods: ['GET'])]
    public function setAdmin(User $user, EntityManagerInterface $manager): Response
    {
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);
        $manager->flush();
        $this->addFlash('success','roles admin affecté avec success');
        return $this->redirectToRoute('profil.listUsers');
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/unsetAdmin/{id}', name: 'unsetAdmin', methods: ['GET'])]
    public function unsetAdmin(User $user, EntityManagerInterface $manager): Response
    {
        $roles = $user->getRoles();
        $key = array_search('ROLE_ADMIN', $roles);
        unset($roles[$key]);
        $user->setRoles($roles);
        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('profil.listUsers');
    }
}