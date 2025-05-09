<?php

namespace Main\PasswordBundle\Security;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Services\AccessManager;
use Main\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PasswordVoter extends Voter
{
    /**
     * @var AccessManager
     */
    protected $accessManager;

    /**
     * @param AccessManager $accessManager
     */
    public function __construct(AccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    const VIEW = 'password_view';
    const DELETE = 'password_delete';
    const EDIT = 'password_edit';
    const LOGS = 'password_logs';
    const VIEW_ACCESS = 'password_view_access';
    const ADD_ACCESS = 'password_add_access';
    const UPDATE_ACCESS = 'password_update_access';
    const DELETE_ACCESS = 'password_update_access';
    const MOVE = 'password_move';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::DELETE, self::EDIT, self::LOGS, self::VIEW_ACCESS, self::ADD_ACCESS, self::UPDATE_ACCESS, self::DELETE_ACCESS, self::MOVE])) {
            return false;
        }

        if (!$subject instanceof Password) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param Password       $subject
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

        /** @var PasswordGroup $post */
        $entity = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->accessManager->hasUserAnyAccessOnPassword($entity, $user);
            case self::EDIT:
            case self::DELETE:
                return $this->accessManager->hasUserModeratorAccessOnPassword($entity, $user);
            case self::LOGS:
            case self::VIEW_ACCESS:
            case self::ADD_ACCESS:
            case self::UPDATE_ACCESS:
            case self::DELETE_ACCESS:
            case self::MOVE:
                return $this->accessManager->hasUserAdminAccessOnPassword($entity, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }
}
