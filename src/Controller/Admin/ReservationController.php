<?php

namespace App\Controller\Admin;

use App\DTO\ReservationRefusedDTO;
use App\Form\ReservationRefusedType;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/reservations', name: 'admin.reservations.')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository, Request $request): Response
    {

        $page = $request->query->getInt('page', 1);
        $filterReservation = (int)$request->query->get('filterReservation');
        $filterUsernameOrEmail = $request->query->get('filterUsernameOrEmail');
        $filterStatus = $request->query->get('filterStatus');
        if ($filterStatus === 'all') {
            $filterStatus = null;
        }

        $reservations = $reservationRepository->paginatedReservationFilters($page, $filterReservation, $filterUsernameOrEmail, $filterStatus);
        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
            'filterReservation' => $filterReservation,
            'filterUsernameOrEmail' => $filterUsernameOrEmail,
            'filterStatus' => $filterStatus,
        ]);
    }

    #[Route('/{id}/validated', name: 'validated', methods: ['GET', 'POST'])]
    public function validated(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservation->setStatus('validated');
        $em->persist($reservation);
        $em->flush();
        return $this->redirectToRoute('admin.reservations.index');
    }

    #[Route('/{id}/delivered', name: 'delivered', methods: ['GET', 'POST'])]
    public function delivered(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservation->setStatus('delivered');
        $em->persist($reservation);
        $em->flush();
        return $this->redirectToRoute('admin.reservations.index');
    }
}