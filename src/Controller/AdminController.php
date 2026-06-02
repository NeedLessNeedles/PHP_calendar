<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Form\ProfileEmailType;
use App\Form\AdminChangePasswordType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;

/**
 * Class AdminController.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
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
     * @param UserRepository $userRepository User repository
     *
     * @return Response HTTP response
     */
    #[Route(
        '/users',
        name: 'app_admin_users',
        methods: ['GET']
    )]
    public function show(UserRepository $userRepository): Response
    {

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * Edit action.
     *
     * @param User $user user
     * @param Request $request request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/users/{id}/edit',
        name: 'app_admin_users_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST']
    )]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // FORM: email
        $emailForm = $this->createForm(ProfileEmailType::class, $user);
        $emailForm->handleRequest($request);

        // FORM: password
        $passwordForm = $this->createForm(AdminChangePasswordType::class);
        $passwordForm->handleRequest($request);

        // EMAIL UPDATE
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $entityManager->flush();
        }

        // PASSWORD UPDATE
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $data = $passwordForm->getData();

            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['newPassword'])
            );

            $entityManager->flush();
        }

        return $this->render('admin/edit.html.twig', [
            'user' => $user,
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/requests', name: 'app_admin_requests', methods: ['GET'])]
    public function requests(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([
            'status' => 'pending'
        ]);

        return $this->render('admin/requests.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/requests/{id}/approve', name: 'app_admin_requests_approve', methods: ['POST'])]
    public function approve(Event $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $event->setStatus('approved');
        $em->flush();

        return $this->redirectToRoute('app_admin_requests');
    }

    #[Route('/requests/{id}/reject', name: 'app_admin_requests_reject', methods: ['POST'])]
    public function reject(Event $event, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em->remove($event);
        $em->flush();

        return $this->redirectToRoute('app_admin_requests');
    }
}
