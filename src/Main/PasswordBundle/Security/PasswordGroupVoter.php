<?php

namespace Main\PasswordBundle\Security;

use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\PasswordBundle\Services\AccessManager;
use Main\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PasswordGroupVoter extends Voter
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

    const VIEW = 'password_group_view';
    const EDIT = 'password_group_edit';
    const DELETE = 'password_group_delete';
    const ALLOW_AS_PARENT = 'password_group_allow_as_parent';
    const MOVE = 'password_group_move';
    const ADD_PASSWORD = 'password_group_add_password';
    const MANAGE_ACCESS = 'password_group_manage_access';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::ADD_PASSWORD, self::ALLOW_AS_PARENT, self::MANAGE_ACCESS, self::DELETE, self::MOVE])) {
            return false;
        }

        if (!$subject instanceof PasswordGroup) {
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

        /** @var PasswordGroup $post */
        $entity = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($entity, $user);
            case self::ADD_PASSWORD:
                return $this->canAddPassword($entity, $user);
            case self::ALLOW_AS_PARENT:
            case self::EDIT:
            case self::MANAGE_ACCESS:
            case self::MOVE:
            case self::DELETE:
                return $this->accessManager->hasUserAdminAccessOnPasswordGroup($entity, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param PasswordGroup $entity
     * @param User          $user
     *
     * @return bool
     */
    private function canView(PasswordGroup $entity, User $user)
    {
        $access = $this->accessManager->getPasswordGroupAccessRight($entity, $user);

        if ($access !== null) {
            return true;
        }

        if ($this->accessManager->hasPasswordAccessByPasswordGroupAndUser($entity, $user)) {
            return true;
        }

        return false;
    }

    /**
     * @param PasswordGroup $entity
     * @param User          $user
     *
     * @return bool
     */
    private function canAddPassword(PasswordGroup $entity, User $user)
    {
        $access = $this->accessManager->getPasswordGroupAccessRight($entity, $user);

        if ($access === null) {
            return false;
        }

        if ($access >= AccessRightModel::RIGHT_MODERATOR) {
            return true;
        }

        return false;
    }
}
