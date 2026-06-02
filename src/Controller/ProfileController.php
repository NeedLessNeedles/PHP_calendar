<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\ProfileEmailType;
use App\Form\ChangePasswordType;

//#[IsGranted('ROLE_USER, ROLE_ADMIN')]
#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route(name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        $emailForm = $this->createForm(ProfileEmailType::class, $user);
        $passwordForm = $this->createForm(ChangePasswordType::class);
        $emailForm->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $entityManager->flush();
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $data = $passwordForm->getData();
            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['newPassword'])
            );
            $entityManager->flush();
        }

        return $this->render('profile/index.html.twig', [
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(): Response
    {
        return $this->render('profile/edit.html.twig', []);
    }
}
