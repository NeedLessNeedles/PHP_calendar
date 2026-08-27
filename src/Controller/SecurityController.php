<?php

/**
 * Security controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\SecurityServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SecurityController.
 */
class SecurityController extends AbstractController
{

    /**
     * Constructor.
     *
     * @param SecurityServiceInterface $securityService Security service
     * @param TranslatorInterface     $translator     Translator
     */
    public function __construct(private readonly SecurityServiceInterface $securityService)
    {
    }

    /**
     * Login action.
     *
     * @return Response HTTP response
     */
    #[Route(
        '/login',
        name: 'app_login',
        methods: ['GET', 'POST'],
    )]
    public function login(): Response
    {

        return $this->render(
            'security/login.html.twig',
            $this->securityService->getLoginData(),
        );
    }

    /**
     * Logout action.
     *
     * @return void void
     */
    #[Route(
        '/logout',
        name: 'app_logout',
        methods: ['GET']
    )]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
