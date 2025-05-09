<?php

namespace Main\FOSUserRecaptchaProtectionBundle\Form\Handler;

use FOS\UserBundle\Model\UserInterface;
use Main\UserBundle\Services\UserManager;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;

class RegistrationFormHandler extends BaseRegistrationFormHandler
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @param bool $confirmation
     */
    protected function onSuccess(UserInterface $user, $confirmation)
    {
        if ($confirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->mailer->sendConfirmationEmailMessage($user);
        } else {
            $user->setEnabled(true);
        }

        $this->userManager->createGivenUser($user);
    }
}
