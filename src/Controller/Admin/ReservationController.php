<?php

namespace App\Controller\Admin;

use App\DTO\ReservationRefusedDTO;
use App\Entity\Creation;
use App\Entity\ReservationItem;
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

/**
 * Controller to manage administrative reservation actions.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/reservations', name: 'admin.reservations.')]
class ReservationController extends AbstractController
{
    /**
     * Handles the rendering of the reservation index page with filtering and pagination.
     *
     * @param ReservationRepository $reservationRepository Repository used to fetch reservation data.
     * @param Request $request The HTTP request instance containing query parameters for filtering and pagination.
     *
     * @return Response The HTTP response rendering the reservation index template with data.
     */
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

    /**
     * Marks the specified reservation as validated.
     *
     * Updates the reservation status to 'validated' and persists it in the database.
     *
     * @param Reservation $reservation The reservation entity to be updated.
     * @param EntityManagerInterface $em The EntityManager used for database operations.
     *
     * @return Response Redirects to the reservation index page after the status update.
     */
    #[Route('/{id}/validated', name: 'validated', methods: ['GET', 'POST'])]
    public function validated(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservation->setStatus('validated');
        $em->persist($reservation);
        $em->flush();
        return $this->redirectToRoute('admin.reservations.index');
    }

    /**
     * Marks a reservation as delivered and updates its status in the database.
     *
     * Persists the updated reservation entity with the "delivered" status and saves the changes
     * to the database. Redirects to the reservation index page after completion.
     *
     * @param Reservation $reservation The reservation entity to be marked as delivered.
     * @param EntityManagerInterface $em The EntityManager used for database operations.
     *
     * @return Response Redirects to the reservation index page after the operation is complete.
     */
    #[Route('/{id}/delivered', name: 'delivered', methods: ['GET', 'POST'])]
    public function delivered(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservation->setStatus('delivered');
        $em->persist($reservation);
        $em->flush();
        return $this->redirectToRoute('admin.reservations.index');
    }

    /**
     * Handles the removal of a reservation and all associated reservation items.
     *
     * Sends a notification email to the reservation user informing them of the reservation update.
     * Updates the associated creations to mark them as unsold, removes the reservation items,
     * and deletes the reservation record from the database.
     *
     * @param Reservation $reservation The reservation entity to be removed.
     * @param EntityManagerInterface $em The EntityManager used for database operations.
     * @param MailerInterface $mailer The mailer service used to send emails.
     *
     * @return Response Redirects to the reservation index page after the operation is complete.
     */
    #[Route('/{id}/remove', name: 'remove', methods: ['GET', 'POST'])]
    public function remove(Reservation $reservation, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $reservationItems = $em->getRepository(ReservationItem::class)->findReservationItemByReservationId($reservation->getId());
        $message = 'reservation refusé';
        $mail = (new TemplatedEmail())
            ->to('edouard.developpement@free.fr')
            ->from($reservation->getUser()->getEmail())
            ->subject('Votre reservation a été modifiée')
            ->htmlTemplate('emails/reservationRefused.html.twig')
            ->context([
                'message' => $message,
                'reservation' => $reservation,
                'reservationItems' => $reservation->getReservationItems()
            ]);;
        $mailer->send($mail);
        foreach ($reservationItems as $reservationItem) {
            $em->getRepository(Creation::class)->find($reservationItem->getCreation()->getId())->setSold(false);
            $em->remove($reservationItem);
            $em->flush();
        }
        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('admin.reservations.index');
    }
}