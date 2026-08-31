<?php

/**
 * Profile controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangeEmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use App\Service\ProfileServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param TranslatorInterface     $translator     Translator
     */
    public function __construct(private readonly ProfileServiceInterface $profileService, private readonly TranslatorInterface $translator)
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
        name: 'app_profile_index',
        methods: ['GET', 'POST']
    )]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/show.html.twig', [
            'user' => $user,
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

    /**
     * Change password action.
     *
     * @param Request $request request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/change_password',
        name: 'app_profile_change_password',
        methods: ['GET', 'POST']
    )]
    public function changePassword(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(
            ChangePasswordType::class,
            null,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('app_profile_change_password'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();

            $this->profileService->savePassword(
                $user,
                $newPassword
            );

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render(
            'profile/change_password.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Change email action.
     *
     * @param Request $request request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/change_email',
        name: 'app_profile_change_email',
        methods: ['GET', 'POST']
    )]
    public function changeEmail(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(
            ChangeEmailType::class,
            null,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('app_profile_change_email'),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $this->profileService->saveEmail(
                $user,
                $email
            );

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render(
            'profile/change_email.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
}
