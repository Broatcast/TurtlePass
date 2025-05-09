<?php

namespace Main\UserBundle\Services;

use Main\PasswordBundle\Services\PasswordAccessManager;
use Main\UserBundle\Entity\User;

class UserDeleteManager
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var PasswordAccessManager
     */
    protected $passwordAccessManager;

    /**
     * @param UserManager           $userManager
     * @param PasswordAccessManager $passwordAccessManager
     */
    public function __construct(UserManager $userManager, PasswordAccessManager $passwordAccessManager)
    {
        $this->userManager = $userManager;
        $this->passwordAccessManager = $passwordAccessManager;
    }

    /**
     * @param User $user
     */
    public function deactivateUser(User $user)
    {
        $user->setEnabled(false);

        $this->userManager->updateUser($user);
    }

    /**
     * @param User $user
     */
    public function activateUser(User $user)
    {
        $user->setEnabled(true);

        $this->userManager->updateUser($user);
    }

    /**
     * @param User $user
     */
    public function deleteUser(User $user)
    {
        $this->passwordAccessManager->deleteAllAccessesUser($user);

        $user->setUsername(sprintf('%s-%s', $user->getUsername(), time()));
        $user->setEmail(sprintf('%s-%s', $user->getEmail(), time()));
        $user->setDeleted(true);

        $this->userManager->updateUser($user);
    }
}
