<?php

namespace Main\AppBundle\Services;

use Main\ApiBundle\Services\SessionTokenManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    /**
     * @var SessionTokenManager
     */
    protected $sessionTokenManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param SessionTokenManager $sessionTokenManager
     * @param Router              $router
     */
    public function __construct(SessionTokenManager $sessionTokenManager, Router $router)
    {
        $this->sessionTokenManager = $sessionTokenManager;
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function onLogoutSuccess(Request $request)
    {
        $this->sessionTokenManager->deleteToken();

        return new RedirectResponse($this->router->generate('fos_user_security_login'));
    }
}
