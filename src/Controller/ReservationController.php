<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Entity\Reservation;
use App\Entity\ReservationItem;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

/**
 * Controller for handling reservation-related operations.
 *
 * This controller provides endpoints for managing reservations, including
 * creating, viewing, updating, finalizing, and removing reservations, as well
 * as managing reservation items. Additionally, it supports specific actions
 * like showing reservations by user and handling carts.
 *
 * @IsGranted("ROLE_USER") Users must have the ROLE_USER to access routes in this controller.
 */
#[IsGranted("ROLE_USER")]
#[Route('/reservations', name: 'reservations.')]
class ReservationController extends AbstractController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    #[Route('/', name: 'index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAll();
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/show/{id}', name: 'show', methods: ['GET', 'POST'])]
    public function show(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservationItems = $em->getRepository(ReservationItem::class)->findReservationItemByReservationId($reservation->getId());

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
            'reservationItems' => $reservationItems,
        ]);
    }

    #[Route('/finalise/{id}', name: 'finalise', methods: ['GET', 'POST'])]
    public function finalise(Reservation            $reservation,
                             Request                $request,
                             EntityManagerInterface $em,
                             MailerInterface        $mailer
    ): Response
    {
        $reservationItems = $em->getRepository(ReservationItem::class)->findReservationItemByReservationId($reservation->getId());
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->get('reservation');
            $mail = (new TemplatedEmail())
                ->to('edouard.developpement@free.fr')
                ->from($reservation->getUser()->getEmail())
                ->subject('Reservation')
                ->htmlTemplate('emails/reservation.html.twig')
                ->context([
                    'data' => $data,
                    'reservation' => $reservation,
                    'reservationItems' => $reservationItems,
                ]);;
            $mailer->send($mail);
            $this->addFlash('success', 'email envoyé');
            $reservation->setStatus('reserved');
            $em->persist($reservation);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('reservation/index.html.twig', [
            'reservation' => $reservation,
            'reservationItems' => $reservationItems,
            'form' => $form,
        ]);

    }

    #[Route('/user', name: 'user')]
    public function showReservationsByUser(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findBy(['User' => $user]);

        return $this->render('reservation/userReservation.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $cart = $this->cartService->getCart();
        $user = $this->getUser();
        $reservation = $em->getRepository(Reservation::class)->findByUserAndStatus($user, 'pending');
        $message = '';
        if ($reservation === null) {
            $reservation = new Reservation();
            $reservation->setUser($user);
            $reservation->setCreatedAt(new \DateTimeImmutable());
            $reservation->setUpdatedAt(new \DateTimeImmutable());
            $reservation->setStatus('pending');
            $em->persist($reservation);
            $em->flush();
            $message = 'La réservation a été créé';
        } else {
            $reservation->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($reservation);
            $em->flush();
            $message = 'La réservation a été modifié';
        }
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setStatus('reserved');
            $em->persist($reservation);
            $em->flush();
            $reservationItemsMail = $em->getRepository(ReservationItem::class)->findReservationItemByReservationId($reservation->getId());

            $mail = (new TemplatedEmail())
                ->to('edouard.developpement@free.fr')
                ->from($reservation->getUser()->getEmail())
                ->subject('une reservation à été effectué')
                ->htmlTemplate('emails/reservation.html.twig')
                ->context([
                    'reservation' => $reservation,
                    'reservationItems' => $reservationItemsMail
                ]);;
            $mailer->send($mail);
            $message = 'Le mail de réservation a été envoyé';
        }

        foreach ($cart as $item) {
            $ReservationItem = new ReservationItem();
            $creation = $em->getRepository(Creation::class)->find($item->getId());
            $ReservationItem->setCreation($creation);
            $ReservationItem->setReservation($reservation);
            $em->persist($ReservationItem);
            $em->flush();
        }
        $reservationItems = $em->getRepository(ReservationItem::class)->findReservationItemByReservationId($reservation->getId());
        $this->cartService->clearCart();
        $this->addFlash('success', $message);
        return $this->render('reservation/index.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'reservationItems' => $reservationItems,
        ]);
    }

    #[Route('/{id}', name: 'remove')]
    public function remove(Reservation $reservation, Request $request, EntityManagerInterface $em): Response
    {
        foreach ($reservation->getReservationItems() as $reservationItem) {
            $creation = $em->getRepository(Creation::class)->find($reservationItem->getCreation()->getId());
            $creation->setUpdatedAt(new \DateTimeImmutable());
            $creation->setSold(false);
            $em->persist($creation);
            $em->flush();
            $em->remove($reservationItem);
            $em->flush();
        }
        $em->remove($reservation);
        $em->flush();
        $this->addFlash('success', 'Reservation supprimer');

        return $this->redirectToRoute('reservations.user');
    }

    #[Route('/reservationItem/{id}', name: 'remove.reservationItem', methods: ['GET', 'POST'])]
    public function removeReservationItem(ReservationItem $reservationItem, Request $request, EntityManagerInterface $em): Response
    {
        $reservationItemId = $reservationItem->getId();
        $creation = $em->getRepository(Creation::class)->find($reservationItem->getCreation()->getId());
        $creation->setSold(false);
        $em->persist($creation);
        $em->remove($reservationItem);
        $em->flush();
        $reservation = $em->getRepository(Reservation::class)->find($reservationItem->getReservation()->getId());
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        $em->persist($reservation);
        $em->flush();

        if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('reservation/deleteReservationItem.html.twig', [
                'reservationItemId' => $reservationItemId,
            ]);
        }

        $this->addFlash('success', 'Article supprimer');

        return $this->redirectToRoute('reservations.user');
    }


}