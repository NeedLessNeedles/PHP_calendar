<?php

/**
 * Registration controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\CustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class RegistrationController.
 */
#[Route('/register')]
class RegistrationController extends AbstractController
{
    public function __construct(private readonly RegistrationServiceInterface $registrationService)
    {
    }
    /**
     * Register action.
     *
     * @param Request                     $request        request
     * @param UserPasswordHasherInterface $passwordHasher passwordHasher
     * @param Security                    $security       login stuff
     * @param EntityManagerInterface      $entityManager  entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_register',
        methods: ['GET', 'POST'],
    )]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $this->registrationService->registerUser(
                $user,
                $plainPassword,
            );

            return $security->login($user, CustomAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
