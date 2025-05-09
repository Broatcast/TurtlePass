<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordUserGroupAccess;
use Main\PasswordBundle\Entity\passwordUserGroupAccessRepository;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupUserGroupAccess;
use Main\PasswordBundle\Entity\passwordGroupUserGroupAccessRepository;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\UserBundle\Entity\User;
use Main\UserBundle\Entity\UserGroup;

class PasswordUserGroupAccessManager
{
    /**
     * @var PasswordGroupUserGroupAccessRepository
     */
    protected $passwordGroupUserGroupAccessRepository;
    
    /**
     * @var PasswordUserGroupAccessRepository
     */
    protected $passwordUserGroupAccessRepository;

    /**
     * @param PasswordGroupUserGroupAccessRepository $passwordGroupUserGroupAccessRepository
     * @param PasswordUserGroupAccessRepository      $passwordUserGroupAccessRepository
     */
    public function __construct(
        PasswordGroupUserGroupAccessRepository $passwordGroupUserGroupAccessRepository,
        PasswordUserGroupAccessRepository $passwordUserGroupAccessRepository)
    {
        $this->passwordGroupUserGroupAccessRepository = $passwordGroupUserGroupAccessRepository;
        $this->passwordUserGroupAccessRepository = $passwordUserGroupAccessRepository;
    }

    /**
     * @param PasswordGroupUserGroupAccess $PasswordGroupUserGroupAccess
     * @param bool                         $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updatePasswordGroupUserGroupAccess(PasswordGroupUserGroupAccess $PasswordGroupUserGroupAccess, $flush = true)
    {
        $this->passwordGroupUserGroupAccessRepository->save($PasswordGroupUserGroupAccess, $flush);
    }

    /**
     * @param PasswordGroupUserGroupAccess $PasswordGroupUserGroupAccess
     * @param bool                         $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removePasswordGroupUserGroupAccess(PasswordGroupUserGroupAccess $PasswordGroupUserGroupAccess, $flush = true)
    {
        $this->passwordGroupUserGroupAccessRepository->remove($PasswordGroupUserGroupAccess, $flush);
    }

    /**
     * @param PasswordUserGroupAccess $PasswordUserGroupAccess
     * @param bool                    $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updatePasswordUserGroupAccess(PasswordUserGroupAccess $PasswordUserGroupAccess, $flush = true)
    {
        $this->passwordUserGroupAccessRepository->save($PasswordUserGroupAccess, $flush);
    }

    /**
     * @param PasswordUserGroupAccess $PasswordUserGroupAccess
     * @param bool                    $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removePasswordUserGroupAccess(PasswordUserGroupAccess $PasswordUserGroupAccess, $flush = true)
    {
        $this->passwordUserGroupAccessRepository->remove($PasswordUserGroupAccess, $flush);
    }

    /**
     * @param UserGroup $userGroup
     * @param User      $user
     *
     * @return PasswordGroupUserGroupAccess[]
     */
    public function findAllPasswordGroupUserGroupAccessesByUserGroup(UserGroup $userGroup, User $user)
    {
        return $this->passwordGroupUserGroupAccessRepository->qbAllByUserGroup($userGroup, $user)->getQuery()->getResult();
    }

    /**
     * @param UserGroup $userGroup
     * @param User      $user
     *
     * @return PasswordUserGroupAccess[]
     */
    public function findAllPasswordUserGroupAccessesByUserGroup(UserGroup $userGroup, User $user)
    {
        return $this->passwordUserGroupAccessRepository->qbAllByUserGroup($userGroup, $user)->getQuery()->getResult();
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllPasswordUserGroupAccessesByPassword(Password $password)
    {
        return $this->passwordUserGroupAccessRepository->qbAllByPassword($password);
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllPasswordGroupUserGroupAccessesByPasswordGroup(PasswordGroup $passwordGroup)
    {
        return $this->passwordGroupUserGroupAccessRepository->qbAllByPasswordGroup($passwordGroup);
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param UserGroup     $userGroup
     * @param int           $right
     *
     * @return PasswordGroupUserGroupAccess
     */
    public function createPasswordGroupUserGroupAccessEntity(PasswordGroup $passwordGroup, UserGroup $userGroup, $right)
    {
        $access = new PasswordGroupUserGroupAccess();
        $access->setPasswordGroup($passwordGroup);
        $access->setUserGroup($userGroup);
        $access->setRight($right);

        return $access;
    }

    /**
     * @param Password  $password
     * @param UserGroup $userGroup
     * @param int       $right
     *
     * @return PasswordUserGroupAccess
     */
    public function createPasswordUserGroupAccessEntity(Password $password, UserGroup $userGroup, $right)
    {
        $access = new PasswordUserGroupAccess();
        $access->setPassword($password);
        $access->setUserGroup($userGroup);
        $access->setRight($right);

        return $access;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param UserGroup     $userGroup
     *
     * @return null|PasswordGroupUserGroupAccess
     */
    public function getPasswordGroupUserGroupAccess(PasswordGroup $passwordGroup, UserGroup $userGroup)
    {
        return $this->passwordGroupUserGroupAccessRepository->findOneBy([
            'passwordGroup' => $passwordGroup->getId(),
            'userGroup' => $userGroup->getId(),
        ]);
    }

    /**
     * @param Password  $password
     * @param UserGroup $userGroup
     *
     * @return null|PasswordUserGroupAccess
     */
    public function getPasswordUserGroupAccess(Password $password, UserGroup $userGroup)
    {
        return $this->passwordUserGroupAccessRepository->findOneBy([
            'password' => $password->getId(),
            'userGroup' => $userGroup->getId(),
        ]);
    }

    /**
     * @param Password  $password
     * @param UserGroup $userGroup
     *
     * @return bool
     */
    public function hasUserGroupAnyAccessOnPassword(Password $password, UserGroup $userGroup)
    {
        $right = $this->getPasswordRight($password, $userGroup);

        if ($right === null) {
            return false;
        }

        return true;
    }

    /**
     * @param Password  $password
     * @param UserGroup $userGroup
     *
     * @return bool
     */
    public function hasUserGroupModeratorAccessOnPassword(Password $password, UserGroup $userGroup)
    {
        $right = $this->getPasswordRight($password, $userGroup);

        if ($right === null) {
            return false;
        }

        if ($right >= AccessRightModel::RIGHT_MODERATOR) {
            return true;
        }

        return false;
    }

    /**
     * @param Password  $password
     * @param UserGroup $userGroup
     *
     * @return bool
     */
    public function hasUserGroupAdminAccessOnPassword(Password $password, UserGroup $userGroup)
    {
        $right = $this->getPasswordRight($password, $userGroup);

        if ($right === null) {
            return false;
        }

        if ($right >= AccessRightModel::RIGHT_ADMIN) {
            return true;
        }

        return false;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param UserGroup     $userGroup
     *
     * @return bool
     */
    public function hasUserGroupAdminAccessOnPasswordGroup(PasswordGroup $passwordGroup, UserGroup $userGroup)
    {
        $right = $this->getPasswordGroupUserGroupAccessRight($passwordGroup, $userGroup);

        if ($right === null) {
            return false;
        }

        if ($right >= AccessRightModel::RIGHT_ADMIN) {
            return true;
        }

        return false;
    }

    /**
     * @param Password  $password
     * @param UserGroup $userGroup
     *
     * @return int|null
     */
    public function getPasswordRight(Password $password, UserGroup $userGroup)
    {
        $right = null;

        $PasswordUserGroupAccess = $this->getPasswordUserGroupAccess($password, $userGroup);

        if ($PasswordUserGroupAccess instanceof PasswordUserGroupAccess) {
            $right = $PasswordUserGroupAccess->getRight();
        }

        $newRight = $this->getPasswordGroupUserGroupAccessRight($password->getPasswordGroup(), $userGroup);

        if ($right === null || ($newRight !== null && $newRight > $right)) {
            $right = $newRight;
        }

        return $right;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param UserGroup     $userGroup
     *
     * @return int|null
     */
    public function getPasswordGroupUserGroupAccessRight(PasswordGroup $passwordGroup, UserGroup $userGroup)
    {
        $right = null;

        $access = $this->getPasswordGroupUserGroupAccess($passwordGroup, $userGroup);

        if ($access instanceof PasswordGroupUserGroupAccess) {
            $right = $access->getRight();
        }

        return $right;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param array         $userGroupIds
     *
     * @return bool
     */
    public function hasPasswordUserGroupAccessByPasswordGroupAndUser(PasswordGroup $passwordGroup, array $userGroupIds)
    {
        return $this->passwordUserGroupAccessRepository->hasPasswordUserGroupAccessByPasswordGroupAndUser($passwordGroup, $userGroupIds);
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return bool
     */
    public function hasUserGroupAnyAccesses(UserGroup $userGroup)
    {
        if ($this->passwordUserGroupAccessRepository->hasUserGroupAnyAccess($userGroup) ||
                $this->passwordGroupUserGroupAccessRepository->hasUserGroupAnyAccess($userGroup)) {
            return true;
        }

        return false;
    }

    /**
     * @param UserGroup $userGroup
     */
    public function deleteAllAccessesUserGroup(UserGroup $userGroup)
    {
        $this->passwordUserGroupAccessRepository->qbDeleteAllByUserGroup($userGroup)->getQuery()->execute();
        $this->passwordGroupUserGroupAccessRepository->qbDeleteAllByUserGroup($userGroup)->getQuery()->execute();
    }
}
