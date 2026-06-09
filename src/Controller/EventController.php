<?php

/**
 * Event controller.
 */

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Form\EventEditType;
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
use Knp\Component\Pager\PaginatorInterface;

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
    public function index(Request $request, EventRepository $eventRepository, PaginatorInterface $paginator): Response
    {
        //$events = $eventRepository->findAll();
        $pagination = $paginator->paginate(
            $eventRepository->queryAll(),
            $request->query->getInt('page', 1),
            EventRepository::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['event.startDate', 'event.title'],
                'defaultSortFieldName' => 'event.startDate',
                'defaultSortDirection' => 'desc',
            ]
        );
        $event = new Event();
        $createForm = $this->createForm(EventType::class, new Event(), [
            'action' => $this->generateUrl('app_event_new'),
        ]);

        $editForm = $this->createForm(EventEditType::class, null, [
            'action' => '#',
        ]);

        return $this->render('event/index.html.twig', [
            'pagination' => $pagination,
            'createForm' => $createForm->createView(),
            'editForm' => $editForm->createView(),
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
        methods: ['POST']
    )]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(
            EventVoter::EDIT,
            $event
        );

        $form = $this->createForm(EventEditType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Event updated');
        } else {
            $this->addFlash('error', 'Validation failed');
        }

        return $this->redirectToRoute('app_event_index');
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

        return $this->redirectToRoute('app_event_index');
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

    #[Route(
        '/{id}/json',
        name: 'app_event_json_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function editEventJson(Event $event): JsonResponse
    {
        return $this->json([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'startDate' => $event->getStartDate()?->format('Y-m-d\TH:i'),
            'endDate' => $event->getEndDate()?->format('Y-m-d\TH:i'),
            'category' => $event->getCategory()?->getId(),
        ]);
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
//        $createForm = $this->createForm(EventType::class, $event);
        $createForm = $this->createForm(EventType::class, new Event(), [
            'action' => $this->generateUrl('app_event_new'),
        ]);
        $editForm = $this->createForm(EventEditType::class, null, [
            'action' => '#',
        ]);

        return $this->render('event/calendar.html.twig', [
            'createForm' => $createForm->createView(),
            'editForm' => $editForm->createView()
        ]);
    }
}
