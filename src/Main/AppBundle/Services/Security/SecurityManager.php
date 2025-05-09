<?php

namespace Main\AppBundle\Services\Security;

use Main\AppBundle\Exceptions\SecurityAccessDeniedException;
use Main\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SecurityManager
{
    /** @var TokenStorage */
    protected $tokenStorage;

    /** @var AuthorizationChecker */
    protected $authorizationChecker;

    /**
     * @param TokenStorage         $tokenStorage
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return null|TokenInterface
     */
    public function getToken()
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * @return null|User
     */
    public function getUser()
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof TokenInterface) {
            return $token->getUser();
        }

        return null;
    }

    /**
     * @return User
     *
     * @throws SecurityAccessDeniedException
     */
    public function getUserOrThrowException()
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $user;
        }

        throw new SecurityAccessDeniedException('User is not logged in.');
    }

    /**
     * @param $attributes
     *
     * @return bool
     */
    public function isGranted($attributes)
    {
        return $this->authorizationChecker->isGranted($attributes);
    }
}
