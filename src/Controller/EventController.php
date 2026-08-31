<?php

/**
 * Event controller.
 */

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Service\EventServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Security\Voter\EventVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EventController.
 */
#[Route('/event')]
class EventController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param EventServiceInterface $eventService Event service
     * @param TranslatorInterface   $translator   Translator
     */
    public function __construct(private readonly EventServiceInterface $eventService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param Request            $request            request
     * @param CategoryRepository $categoryRepository Category repository
     * @param TagRepository      $tagRepository      Tag repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_event_index',
        methods: ['GET']
    )]
    public function index(Request $request, CategoryRepository $categoryRepository, TagRepository $tagRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $categoryId = $request->query->get('categoryId');
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;
        $tagId = $request->query->get('tagId');
        $tagId = is_numeric($tagId) ? (int) $tagId : null;
        $title = $request->query->get('title');

        $pagination = $this->eventService->getPaginatedList(
            $page,
            $categoryId,
            $title,
            $tagId
        );

        return $this->render('event/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $categoryId ?: null,
            'tags' => $tagRepository->findAll(),
            'currentTag' => $tagId,
            'title' => $title,
        ]);
    }

    /**
     * New action.
     *
     * @param Request $request request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/new',
        name: 'app_event_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request): Response
    {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->eventService->canBeEmpty($event)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.empty_title')
                );

                return $this->redirectToRoute('app_event_new');
            }

            if (!$this->eventService->isTitleUnique($event)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.title_already_exists')
                );

                return $this->redirectToRoute('app_event_new');
            }

            if ($form->isValid()) {
                $this->eventService->save($event, $this->getUser());

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('app_event_index');
            }
        }

        return $this->render(
            'event/new.html.twig',
            ['form' => $form->createView()]
        );
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
     * @param Request $request request
     * @param Event   $event   event
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'app_event_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'PUT'],
    )]
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(
            EventType::class,
            $event,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('app_event_edit', ['id' => $event->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->eventService->canBeEmpty($event)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.empty_title')
                );

                return $this->redirectToRoute(
                    'app_event_edit',
                    ['id' => $event->getId()]
                );
            }

            if (!$this->eventService->isTitleUnique($event)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.title_already_exists')
                );

                return $this->redirectToRoute(
                    'app_event_edit',
                    ['id' => $event->getId()]
                );
            }

            if ($form->isValid()) {
                $this->eventService->save($event, $this->getUser());

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('app_event_index');
            }
        }

        return $this->render(
            'event/edit.html.twig',
            [
                'form' => $form->createView(),
                'event' => $event,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request request
     * @param Event   $event   event
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'app_event_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    public function delete(Request $request, Event $event): Response
    {
        // $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);

        $form = $this->createForm(EventType::class, $event, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('app_event_delete', ['id' => $event->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventService->delete($event);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('app_event_index');
        }

        return $this->render(
            'event/delete.html.twig',
            [
                'form' => $form->createView(),
                'event' => $event,
            ]
        );
    }

    /**
     * Export events list to ICS format.
     *
     * @return Response HTTP response
     */
    #[Route(
        '/export',
        name: 'app_event_export',
        methods: ['GET']
    )]
    public function exportIcs(): Response
    {
        $icsContent = $this->eventService->exportToIcs();

        return new Response(
            $icsContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="events.ics"',
            ]
        );
    }

    /**
     * EventsJson action.
     *
     * @param EventRepository $eventRepository event repository
     *
     * @return JsonResponse JSON response
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
     * editEventJson action.
     *
     * @param Event $event event
     *
     * @return JsonResponse JSON response
     */
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
        $createForm = $this->createForm(EventType::class, new Event(), [
            'action' => $this->generateUrl('app_event_new'),
        ]);

        return $this->render('event/calendar.html.twig', [
            'createForm' => $createForm->createView(),
        ]);
    }
}
