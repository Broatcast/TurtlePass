<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordAccess;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupAccess;
use Main\PasswordBundle\Entity\PasswordGroupUserGroupAccess;
use Main\PasswordBundle\Entity\PasswordUserGroupAccess;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\UserBundle\Entity\User;

class AccessManager
{
    /**
     * @var PasswordAccessManager
     */
    protected $passwordAccessManager;

    /**
     * @var PasswordUserGroupAccessManager
     */
    protected $passwordUserGroupAccessManager;

    /**
     * @param PasswordAccessManager          $passwordAccessManager
     * @param PasswordUserGroupAccessManager $passwordUserGroupAccessManager
     */
    public function __construct(PasswordAccessManager $passwordAccessManager, PasswordUserGroupAccessManager $passwordUserGroupAccessManager)
    {
        $this->passwordAccessManager = $passwordAccessManager;
        $this->passwordUserGroupAccessManager = $passwordUserGroupAccessManager;
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return bool
     */
    public function hasUserAnyAccessOnPassword(Password $password, User $user)
    {
        if ($this->passwordAccessManager->hasUserAnyAccessOnPassword($password, $user)) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            if ($this->passwordUserGroupAccessManager->hasUserGroupAnyAccessOnPassword($password, $userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return bool
     */
    public function hasUserModeratorAccessOnPassword(Password $password, User $user)
    {
        if ($this->passwordAccessManager->hasUserModeratorAccessOnPassword($password, $user)) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            if ($this->passwordUserGroupAccessManager->hasUserGroupModeratorAccessOnPassword($password, $userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return bool
     */
    public function hasUserAdminAccessOnPassword(Password $password, User $user)
    {
        if ($this->passwordAccessManager->hasUserAdminAccessOnPassword($password, $user)) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            if ($this->passwordUserGroupAccessManager->hasUserGroupAdminAccessOnPassword($password, $userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return int|null
     */
    public function getPasswordGroupAccessRight(PasswordGroup $passwordGroup, User $user)
    {
        $right = $this->passwordAccessManager->getPasswordGroupAccessRight($passwordGroup, $user);

        if ($right === AccessRightModel::RIGHT_ADMIN) {
            return $right;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            $result = $this->passwordUserGroupAccessManager->getPasswordGroupUserGroupAccessRight($passwordGroup, $userGroup);

            if ($result !== null && ($right === null || $result > $right)) {
                $right = $result;
            }
        }

        return $right;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return bool
     */
    public function hasUserAdminAccessOnPasswordGroup(PasswordGroup $passwordGroup, User $user)
    {
        if ($this->passwordAccessManager->hasUserAdminAccessOnPasswordGroup($passwordGroup, $user)) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            if ($this->passwordUserGroupAccessManager->hasUserGroupAdminAccessOnPasswordGroup($passwordGroup, $userGroup)) {
                return true;
            }
        }
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return bool
     */
    public function hasPasswordAccessByPasswordGroupAndUser(PasswordGroup $passwordGroup, User $user)
    {
        if ($this->passwordAccessManager->hasPasswordAccessByPasswordGroupAndUser($passwordGroup, $user)) {
            return true;
        }

        $userGroupIds = [];

        foreach ($user->getUserGroups() as $userGroup) {
            $userGroupIds[] = $userGroup->getId();
        }

        if (\count($userGroupIds)) {
            if ($this->passwordUserGroupAccessManager->hasPasswordUserGroupAccessByPasswordGroupAndUser($passwordGroup, $userGroupIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User           $user
     * @param PasswordAccess $passwordAccess
     *
     * @return bool
     */
    public function isEditingPasswordAccessAllowed(User $user, PasswordAccess $passwordAccess)
    {
        if ($user->getId() !== $passwordAccess->getUser()->getId()) {
            return true;
        }

        $right = $passwordAccess->getRight();

        $checkRight = $this->passwordAccessManager->getPasswordGroupAccessRight($passwordAccess->getPassword()->getPasswordGroup(), $user);
        if ($checkRight !== null && $checkRight >= $right) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            $checkRight = $this->passwordUserGroupAccessManager->getPasswordRight($passwordAccess->getPassword(), $userGroup);
            if ($checkRight !== null && $checkRight >= $right) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User                $user
     * @param PasswordGroupAccess $passwordGroupAccess
     *
     * @return bool
     */
    public function isEditingPasswordGroupAccessAllowed(User $user, PasswordGroupAccess $passwordGroupAccess)
    {
        if ($user->getId() !== $passwordGroupAccess->getUser()->getId()) {
            return true;
        }

        $passwordGroup = $passwordGroupAccess->getPasswordGroup();
        $right = $passwordGroupAccess->getRight();

        foreach ($user->getUserGroups() as $userGroup) {
            $checkRight = $this->passwordUserGroupAccessManager->getPasswordGroupUserGroupAccessRight($passwordGroup, $userGroup);
            if ($checkRight !== null && $checkRight >= $right) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User                    $user
     * @param PasswordUserGroupAccess $passwordUserGroupAccess
     *
     * @return bool
     */
    public function isEditingPasswordUserGroupAccessAllowed(User $user, PasswordUserGroupAccess $passwordUserGroupAccess)
    {
        $found = false;
        foreach ($user->getUserGroups() as $userGroup) {
            if ($userGroup->getId() === $passwordUserGroupAccess->getUserGroup()->getId()) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return true;
        }

        $right = $passwordUserGroupAccess->getRight();

        $checkRight = $this->passwordAccessManager->getPasswordRight($passwordUserGroupAccess->getPassword(), $user);
        if ($checkRight !== null && $checkRight >= $right) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            if ($userGroup->getId() !== $passwordUserGroupAccess->getUserGroup()->getId()) {
                $checkRight = $this->passwordUserGroupAccessManager->getPasswordRight($passwordUserGroupAccess->getPassword(), $userGroup);
                if ($checkRight !== null && $checkRight >= $right) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param User                         $user
     * @param PasswordGroupUserGroupAccess $passwordGroupUserGroupAccess
     *
     * @return bool
     */
    public function isEditingPasswordGroupUserGroupAccessAllowed(User $user, PasswordGroupUserGroupAccess $passwordGroupUserGroupAccess)
    {
        $found = false;
        foreach ($user->getUserGroups() as $userGroup) {
            if ($userGroup->getId() === $passwordGroupUserGroupAccess->getUserGroup()->getId()) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return true;
        }

        $passwordGroup = $passwordGroupUserGroupAccess->getPasswordGroup();
        $right = $passwordGroupUserGroupAccess->getRight();

        $checkRight = $this->passwordAccessManager->getPasswordGroupAccessRight($passwordGroupUserGroupAccess->getPasswordGroup(), $user);
        if ($checkRight !== null && $checkRight >= $right) {
            return true;
        }

        foreach ($user->getUserGroups() as $userGroup) {
            if ($userGroup->getId() !== $passwordGroupUserGroupAccess->getUserGroup()->getId()) {
                $checkRight = $this->passwordUserGroupAccessManager->getPasswordGroupUserGroupAccessRight($passwordGroup, $userGroup);
                if ($checkRight !== null && $checkRight >= $right) {
                    return true;
                }
            }
        }

        return false;
    }
}
