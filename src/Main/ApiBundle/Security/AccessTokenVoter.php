<?php

namespace Main\ApiBundle\Security;

use Main\ApiBundle\Entity\AccessToken;
use Main\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessTokenVoter extends Voter
{
    const VIEW = 'access_token_view';
    const DELETE = 'access_token_delete';
    const EDIT = 'access_token_edit';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::DELETE, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof AccessToken) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param AccessToken    $subject
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

        if ($user->getId() != $subject->getUser()->getId()) {
            return false;
        }

        if ($subject->isDefault()) {
            return false;
        }

        if (!$subject->isCustom()) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::DELETE:
            case self::EDIT:
                return true;
        }

        throw new \LogicException('This code should not be reached!');
    }
}
