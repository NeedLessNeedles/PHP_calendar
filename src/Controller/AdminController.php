<?php

/**
 * Admin controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Form\ProfileEmailType;
use App\Form\AdminChangePasswordType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;
use App\Service\EventServiceInterface;
use App\Service\AdminServiceInterface;
use App\Service\ProfileServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AdminController.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param ProfileServiceInterface $profileService Profile service
     * @param AdminServiceInterface   $adminService   Admin service
     * @param EventServiceInterface   $eventService   Event service
     * @param TranslatorInterface     $translator     Translator
     */
    public function __construct(private readonly ProfileServiceInterface $profileService, private readonly AdminServiceInterface $adminService, private readonly EventServiceInterface $eventService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_admin',
        methods: ['GET']
    )]
    public function index(): Response
    {

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * Show action.
     *
     * @param Request        $request        Request
     * @param UserRepository $userRepository User repository
     *
     * @return Response HTTP response
     */
    #[Route(
        '/users',
        name: 'app_admin_users',
        methods: ['GET']
    )]
    public function show(Request $request, UserRepository $userRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->profileService->getPaginatedList($page);

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * Edit action.
     *
     * @param User                   $user          user
     * @param Request                $request       request
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/users/{id}/edit',
        name: 'app_admin_users_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST']
    )]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // FORM: email
        $emailForm = $this->createForm(ProfileEmailType::class, $user);
        $emailForm->handleRequest($request);

        // FORM: password
        $passwordForm = $this->createForm(AdminChangePasswordType::class);
        $passwordForm->handleRequest($request);

        // email update
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );
        } else {
            $this->addFlash(
                'error',
                $this->translator->trans('message.validation_failed')
            );
        }

        // password update
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $data = $passwordForm->getData();

            $this->adminService->changePassword(
                $user,
                $data['newPassword']
            );
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );
        } else {
            $this->addFlash(
                'error',
                $this->translator->trans('message.validation_failed')
            );
        }

        return $this->render('admin/edit.html.twig', [
            'user' => $user,
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    /**
     * Block action.
     *
     * @param User                   $user          user
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/users/{id}/block',
        name: 'app_admin_users_block',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function block(User $user, EntityManagerInterface $entityManager): Response
    {
        $this->adminService->toggleBlock($user, $this->getUser());
        $entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('message.blocked_user')
        );

        return $this->redirectToRoute('app_admin_users');
    }

    /**
     * Requests action.
     *
     * @param Request         $request         request
     * @param EventRepository $eventRepository Event Repository
     *
     * @return Response HTTP response
     */
    #[Route(
        '/requests',
        name: 'app_admin_requests',
        methods: ['GET']
    )]
    public function requests(Request $request, EventRepository $eventRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->eventService->getPaginatedList(
            $page,
            status: 'pending'
        );
        $events = $eventRepository->findBy([
            'status' => 'pending',
        ]);

        return $this->render('admin/requests.html.twig', [
            'events' => $events,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Approve action.
     *
     * @param Event                  $event         event
     * @param EntityManagerInterface $entityManager Entity Manager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/requests/{id}/approve',
        name: 'app_admin_requests_approve',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function approve(Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->adminService->approveEvent($event);
        $entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('message.request_approved')
        );

        return $this->redirectToRoute('app_admin_requests');
    }

    /**
     * Reject action.
     *
     * @param Event                  $event         event
     * @param EntityManagerInterface $entityManager Entity Manager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/requests/{id}/reject',
        name: 'app_admin_requests_reject',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function reject(Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('message.request_rejected')
        );

        return $this->redirectToRoute('app_admin_requests');
    }
}
