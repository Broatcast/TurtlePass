<?php

namespace Main\ApiBundle\Services;

use Main\ApiBundle\Entity\AccessToken;
use Main\AppBundle\Services\Security\SecurityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionTokenManager
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var TokenManager
     */
    protected $tokenManager;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @param RequestStack    $requestStack
     * @param TokenManager    $tokenManager
     * @param SecurityManager $securityManager
     * @param string          $sessionKey
     */
    public function __construct(RequestStack $requestStack, TokenManager $tokenManager, SecurityManager $securityManager, $sessionKey)
    {
        $this->requestStack = $requestStack;
        $this->tokenManager = $tokenManager;
        $this->securityManager = $securityManager;
        $this->sessionKey = $sessionKey;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        $token = $this->getSessionToken();

        if (!is_null($token)) {
            $accessToken = $this->tokenManager->getOneOrNullByToken($token);

            if ($accessToken instanceof AccessToken) {
                $accessToken->setExpiresAt(time() + 86400);
                $this->tokenManager->updateAccessToken($accessToken);

                return $token;
            }
        }

        $generatedToken = $this->tokenManager->generateToken();

        $accessToken = $this->tokenManager->createAccessTokenEntity();
        $accessToken->setUser($this->securityManager->getUser());
        $accessToken->setClient($this->tokenManager->getDefaultClient());
        $accessToken->setDefault(true);
        $accessToken->setExpiresAt(time() + 86400);
        $accessToken->setToken($generatedToken);

        $this->tokenManager->updateAccessToken($accessToken);

        $this->setSessionToken($generatedToken);

        return $generatedToken;
    }

    public function deleteToken()
    {
        $token = $this->getSessionToken();

        if (!is_null($token)) {
            $accessToken = $this->tokenManager->getOneOrNullByToken($token);

            if ($accessToken instanceof AccessToken) {
                $this->tokenManager->removeAccessToken($accessToken);
            }

            $this->deleteSessionToken();
        }
    }

    protected function deleteSessionToken()
    {
        $this->requestStack->getCurrentRequest()->getSession()->set($this->sessionKey, null);
    }

    /**
     * @param string $token
     */
    protected function setSessionToken($token)
    {
        $this->requestStack->getCurrentRequest()->getSession()->set($this->sessionKey, $token);
    }

    /**
     * @return null|string
     */
    protected function getSessionToken()
    {
        return $this->requestStack->getCurrentRequest()->getSession()->get($this->sessionKey);
    }
}
