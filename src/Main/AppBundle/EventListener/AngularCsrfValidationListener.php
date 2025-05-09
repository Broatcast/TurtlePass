<?php

namespace Main\AppBundle\EventListener;

use Dunglas\AngularCsrfBundle\Csrf\AngularCsrfTokenManager;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Main\ApiBundle\Entity\AccessToken;
use Main\ApiBundle\Services\TokenManager;
use Main\AppBundle\Services\Security\SecurityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AngularCsrfValidationListener
{
    /**
     * @var AngularCsrfTokenManager
     */
    protected $angularCsrfTokenManager;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var TokenManager
     */
    protected $tokenManager;

    /**
     * @var string
     */
    protected $headerName;

    /**
     * @param AngularCsrfTokenManager $angularCsrfTokenManager
     * @param SecurityManager         $securityManager
     * @param TokenManager            $tokenManager
     * @param string                  $headerName
     */
    public function __construct(AngularCsrfTokenManager $angularCsrfTokenManager, SecurityManager $securityManager, TokenManager $tokenManager, $headerName)
    {
        $this->angularCsrfTokenManager = $angularCsrfTokenManager;
        $this->securityManager = $securityManager;
        $this->tokenManager = $tokenManager;
        $this->headerName = $headerName;
    }

    /**
     * Handles CSRF token validation.
     *
     * @param GetResponseEvent $event
     *
     * @throws AccessDeniedHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequest()->getMethod() != 'GET' && preg_match('#^/api#', $event->getRequest()->getPathInfo())) {
            $token = $this->securityManager->getToken();

            if ($token instanceof OAuthToken) {
                $accessToken = $this->tokenManager->getOneOrNullByToken($token->getToken());

                if (!$accessToken instanceof AccessToken) {
                    throw new AccessDeniedHttpException('Access Token not found.');
                }

                if ($accessToken->isDefault()) {
                    $value = $event->getRequest()->headers->get($this->headerName);

                    if (!$value || !$this->angularCsrfTokenManager->isTokenValid($value)) {
                        throw new AccessDeniedHttpException('Bad CSRF token.');
                    }
                }
            }
        }
    }
}
