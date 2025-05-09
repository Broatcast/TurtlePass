<?php

namespace Main\UserBundle\Security;

use Main\UserBundle\Entity\LdapUser;
use Main\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const CHANGE_PASSWORD = 'change_password';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CHANGE_PASSWORD])) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CHANGE_PASSWORD:
                if ($user instanceof LdapUser) {
                    return false;
                }

                return true;
        }

        return false;
    }
}
