<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEmailType;
use App\Form\AdminChangePasswordType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route(name: 'app_admin', methods: ['GET'])]
    public function index(): Response
    {

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function show(UserRepository $userRepository): Response
    {

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // FORM: email
        $emailForm = $this->createForm(ProfileEmailType::class, $user);
        $emailForm->handleRequest($request);

        // FORM: password (NIE mapujemy na User entity)
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
}
