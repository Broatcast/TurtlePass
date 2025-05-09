<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordAccess;
use Main\PasswordBundle\Entity\PasswordAccessRepository;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupAccess;
use Main\PasswordBundle\Entity\PasswordGroupAccessRepository;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\UserBundle\Entity\User;

class PasswordAccessManager
{
    /**
     * @var PasswordGroupAccessRepository
     */
    protected $passwordGroupAccessRepository;

    /**
     * @var PasswordAccessRepository
     */
    protected $passwordAccessRepository;

    /**
     * @param PasswordGroupAccessRepository $passwordGroupAccessRepository
     * @param PasswordAccessRepository      $passwordAccessRepository
     */
    public function __construct(PasswordGroupAccessRepository $passwordGroupAccessRepository, PasswordAccessRepository $passwordAccessRepository)
    {
        $this->passwordGroupAccessRepository = $passwordGroupAccessRepository;
        $this->passwordAccessRepository = $passwordAccessRepository;
    }

    /**
     * @param PasswordGroupAccess $passwordGroupAccess
     * @param bool                $flush
     */
    public function updatePasswordGroupAccess(PasswordGroupAccess $passwordGroupAccess, $flush = true)
    {
        $this->passwordGroupAccessRepository->save($passwordGroupAccess, $flush);
    }

    /**
     * @param PasswordGroupAccess $passwordGroupAccess
     * @param bool                $flush
     */
    public function removePasswordGroupAccess(PasswordGroupAccess $passwordGroupAccess, $flush = true)
    {
        $this->passwordGroupAccessRepository->remove($passwordGroupAccess, $flush);
    }

    /**
     * @param PasswordAccess $passwordAccess
     * @param bool           $flush
     */
    public function updatePasswordAccess(PasswordAccess $passwordAccess, $flush = true)
    {
        $this->passwordAccessRepository->save($passwordAccess, $flush);
    }

    /**
     * @param PasswordAccess $passwordAccess
     * @param bool           $flush
     */
    public function removePasswordAccess(PasswordAccess $passwordAccess, $flush = true)
    {
        $this->passwordAccessRepository->remove($passwordAccess, $flush);
    }

    /**
     * @param User $user
     *
     * @return PasswordGroupAccess[]
     */
    public function findAllPasswordGroupAccessesByUser(User $user)
    {
        return $this->passwordGroupAccessRepository->qbAllByUser($user)->getQuery()->getResult();
    }

    /**
     * @param User $user
     *
     * @return PasswordAccess[]
     */
    public function findAllPasswordAccessesByUser(User $user)
    {
        return $this->passwordAccessRepository->qbAllByUser($user)->getQuery()->getResult();
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllPasswordAccessesByPassword(Password $password)
    {
        return $this->passwordAccessRepository->qbAllByPassword($password);
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllPasswordGroupAccessesByPasswordGroup(PasswordGroup $passwordGroup)
    {
        return $this->passwordGroupAccessRepository->qbAllByPasswordGroup($passwordGroup);
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     * @param int           $right
     *
     * @return PasswordGroupAccess
     */
    public function createPasswordGroupAccessEntity(PasswordGroup $passwordGroup, User $user, $right)
    {
        $access = new PasswordGroupAccess();
        $access->setPasswordGroup($passwordGroup);
        $access->setUser($user);
        $access->setRight($right);

        return $access;
    }

    /**
     * @param Password $password
     * @param User     $user
     * @param int      $right
     *
     * @return PasswordAccess
     */
    public function createPasswordAccessEntity(Password $password, User $user, $right)
    {
        $access = new PasswordAccess();
        $access->setPassword($password);
        $access->setUser($user);
        $access->setRight($right);

        return $access;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return null|PasswordGroupAccess
     */
    public function getPasswordGroupAccess(PasswordGroup $passwordGroup, User $user)
    {
        return $this->passwordGroupAccessRepository->findOneBy([
            'passwordGroup' => $passwordGroup->getId(),
            'user' => $user->getId(),
        ]);
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return null|PasswordAccess
     */
    public function getPasswordAccess(Password $password, User $user)
    {
        return $this->passwordAccessRepository->findOneBy([
            'password' => $password->getId(),
            'user' => $user->getId(),
        ]);
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return bool
     */
    public function hasUserAnyAccessOnPassword(Password $password, User $user)
    {
        $right = $this->getPasswordRight($password, $user);

        if ($right === null) {
            return false;
        }

        return true;
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return bool
     */
    public function hasUserModeratorAccessOnPassword(Password $password, User $user)
    {
        $right = $this->getPasswordRight($password, $user);

        if ($right === null) {
            return false;
        }

        if ($right >= AccessRightModel::RIGHT_MODERATOR) {
            return true;
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
        $right = $this->getPasswordRight($password, $user);

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
     * @param User          $user
     *
     * @return bool
     */
    public function hasUserAdminAccessOnPasswordGroup(PasswordGroup $passwordGroup, User $user)
    {
        $right = $this->getPasswordGroupAccessRight($passwordGroup, $user);

        if ($right === null) {
            return false;
        }

        if ($right >= AccessRightModel::RIGHT_ADMIN) {
            return true;
        }

        return false;
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return int|null
     */
    public function getPasswordRight(Password $password, User $user)
    {
        $right = null;

        $passwordAccess = $this->getPasswordAccess($password, $user);

        if ($passwordAccess instanceof PasswordAccess) {
            $right = $passwordAccess->getRight();
        }

        $newRight = $this->getPasswordGroupAccessRight($password->getPasswordGroup(), $user);

        if ($right === null || ($newRight !== null && $newRight > $right)) {
            $right = $newRight;
        }

        return $right;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return int|null
     */
    public function getPasswordGroupAccessRight(PasswordGroup $passwordGroup, User $user)
    {
        $right = null;

        $access = $this->getPasswordGroupAccess($passwordGroup, $user);

        if ($access instanceof PasswordGroupAccess) {
            $right = $access->getRight();
        }

        return $right;
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return bool
     */
    public function hasPasswordAccessByPasswordGroupAndUser(PasswordGroup $passwordGroup, User $user)
    {
        return $this->passwordAccessRepository->hasPasswordAccessByPasswordGroupAndUser($passwordGroup, $user);
    }

    /**
     * @param User $user
     */
    public function deleteAllAccessesUser(User $user)
    {
        $this->passwordAccessRepository->qbDeleteAllByUser($user)->getQuery()->execute();
        $this->passwordGroupAccessRepository->qbDeleteAllByUser($user)->getQuery()->execute();
    }
}
