<?php

/**
 * Event controller.
 */

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\Voter\EventVoter;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class EventController.
 */
#[Route('/event')]
class EventController extends AbstractController
{
    /**
     * Index action.
     *
     * @param EventRepository $eventRepository Event repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_event_index',
        methods: ['GET']
    )]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'form' => $form->createView()
        ]);
    }

    /**
     * New action.
     *
     * @param Request                $request            request
     * @param EntityManagerInterface $entityManager      Entity manager
     * @param CategoryRepository     $categoryRepository Category repo
     *
     * @return Response HTTP response
     */
    #[Route(
        '/new',
        name: 'app_event_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

//        if ($form->isSubmitted()) {
//            dd($form->isValid(), (string) $form->getErrors(true));
//        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $event->setStatus('approved');
            } elseif ($user) {
                $event->setStatus('approved');
            } else {
                $event->setStatus('pending');
            }

            if (!$event->getCategory()) {
                throw $this->createNotFoundException('Category is required');
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

    /**
     * Show action.
     *
     * @param Event $event event
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'app_event_show',
        requirements: ['id' => '[1-9]\d*'],
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

    /**
     * Edit action.
     *
     * @param Request                $request       request
     * @param Event                  $event         event
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'app_event_edit',
        requirements: ['id' => '[1-9]\d*'],
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

    /**
     * Delete action.
     *
     * @param Request                $request       request
     * @param Event                  $event         event
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'app_event_delete',
        requirements: ['id' => '[1-9]\d*'],
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

    /**
     * EventsJson action.
     *
     * @param EventRepository $eventRepository event repository
     *
     * @return JsonResponse HTTP response
     */
    #[Route(
        '/json',
        name: 'app_event_json',
        methods: ['GET']
    )]
    public function eventsJson(EventRepository $eventRepository): JsonResponse
    {
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

    /**
     * Calendar action.
     *
     * @return Response HTTP response
     */
    #[Route(
        '/calendar',
        name: 'app_event_calendar',
        methods: ['GET', 'POST']
    )]
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
