<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\Voter\EventVoter;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
//        if ($this->isGranted('ROLE_ADMIN')) {
//            $events = $eventRepository->findAll();
//        } else {
//            $events = $eventRepository->findBy([
//                'owner' => $this->getUser(),
//            ]);
//        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $event->setStatus('approved');
            } elseif ($user) {
                $event->setStatus('approved');
            } else {
                $event->setStatus('pending');
            }

            $event->setOwner($user);

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_calendar', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route(
        '/{id}',
        name: 'app_event_show',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function show(Event $event): Response
    {
        $this->denyAccessUnlessGranted(
            EventVoter::VIEW,
            $event
        );

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route(
        '/{id}/edit',
        name: 'app_event_edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(
            EventVoter::EDIT,
            $event
        );

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route(
        '/{id}',
        name: 'app_event_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_calendar');
    }

    #[Route('/json', name: 'app_event_json', methods: ['GET'])]
    public function eventsJson(EventRepository $eventRepository): JsonResponse
    {
//        if ($this->isGranted('ROLE_ADMIN')) {
//            $events = $eventRepository->findAll();
//        } elseif ($this->getUser()) {
//            $events = $eventRepository->findBy([
//                'status' => 'approved',
//                'owner' => $this->getUser(),
//            ]);
//        } else {
//            $events = $eventRepository->findBy([
//                'status' => 'pending',
//            ]);
//        }
        $events = $eventRepository->findAll();

        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
                'end' => $event->getEndDate()?->format('Y-m-d\TH:i:s'),
                'status' => $event->getStatus(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/calendar', name: 'app_event_calendar', methods: ['GET', 'POST'])]
    public function calendar(): Response
    {
        $event = new Event();
        $event->setOwner($this->getUser());
        $form = $this->createForm(EventType::class, $event);

        return $this->render('event/calendar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
