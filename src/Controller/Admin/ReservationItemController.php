<?php

namespace App\Controller\Admin;

use App\DTO\ReservationRefusedDTO;
use App\Entity\Creation;
use App\Entity\Reservation;
use App\Entity\ReservationItem;
use App\Form\ReservationRefusedType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/reservations/items', name: 'admin.reservation.items.')]
class ReservationItemController extends AbstractController
{
    #[Route('/{id}', name: 'index', methods: ['GET', 'POST'])]
    public function index(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservationItems = $em->getRepository(ReservationItem::class)->findBy(['Reservation' => $reservation]);
        return $this->render('admin/reservation/reservationItem.html.twig', [
            'reservationItems' => $reservationItems,
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/refused', name: 'refused', methods: ['GET', 'POST'])]
    public function removeReservationItem(ReservationItem $reservationItem,
                                          Request $request,
                                          EntityManagerInterface $em,
                                          MailerInterface $mailer
        ): Response
    {
        $data = new ReservationRefusedDTO();
        $form = $this->createForm(ReservationRefusedType::class, $data);
        $reservationItemId = $reservationItem->getId();
        $reservation = $reservationItem->getReservation();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $creation = $em->getRepository(Creation::class)->find($reservationItem->getCreation()->getId());
            $creation->setSold(false);
            $em->persist($creation);
            $em->remove($reservationItem);
            $em->flush();
            if ($reservation->getReservationItems()->count() === 0){
                $em->remove($reservation);
                $message = $data->message;
                $mail = (new TemplatedEmail())
                    ->to('edouard.developpement@free.fr')
                    ->from($reservation->getUser()->getEmail())
                    ->subject('votre reservation à été annulé')
                    ->htmlTemplate('emails/reservationRefused.html.twig')
                    ->context([
                        'message' => $message,
                        'reservation' => $reservation,
                        'reservationItems' => $reservation->getReservationItems()
                    ]);;
                $mailer->send($mail);
            }
            else{
                $reservation->setUpdatedAt(new \DateTimeImmutable());
                $em->persist($reservation);
                $em->flush();
                $message = $data->message;
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
            }

            $this->addFlash('success', 'Item removed');
            return $this->redirectToRoute('reservations.user');

        }

        return $this->render('admin/reservation/refused.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

}