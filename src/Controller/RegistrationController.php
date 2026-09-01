<?php

/**
 * Registration controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\CustomAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\RegistrationServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationController.
 */
#[Route('/register')]
class RegistrationController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param RegistrationServiceInterface $registrationService Registration service
     * @param TranslatorInterface          $translator          Translator
     */
    public function __construct(private readonly RegistrationServiceInterface $registrationService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Register action.
     *
     * @param Request  $request  request
     * @param Security $security login stuff
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_register',
        methods: ['GET', 'POST'],
    )]
    public function register(Request $request, Security $security): Response
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

            $this->addFlash(
                'success',
                $this->translator->trans('message.user_registered')
            );

            return $security->login($user, CustomAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
