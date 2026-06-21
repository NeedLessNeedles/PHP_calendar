<?php

/**
 * Profile controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProfileEmailType;
use App\Form\ChangePasswordType;
use App\Service\ProfileServiceInterface;

/**
 * Class ProfileController.
 */
#[Route('/profile')]
class ProfileController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param ProfileServiceInterface $profileService Profile service
     */
    public function __construct(private readonly ProfileServiceInterface $profileService)
    {
    }

    /**
     * Index action.
     *
     * @param Request                $request       request
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_profile',
        methods: ['GET', 'POST']
    )]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // FORM: email
        $emailForm = $this->createForm(ProfileEmailType::class, $user);
        $emailForm->handleRequest($request);

        // FORM: password
        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        // email update
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $entityManager->flush();
        }

        // password update
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $data = $passwordForm->getData();

            $this->profileService->changePassword(
                $user,
                $data['newPassword']
            );

            $entityManager->flush();
        }

        return $this->render('profile/index.html.twig', [
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    /**
     * Edit action.
     *
     * @return Response HTTP response
     */
    #[Route(
        '/edit',
        name: 'app_profile_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(): Response
    {
        return $this->render('profile/edit.html.twig', []);
    }
}
