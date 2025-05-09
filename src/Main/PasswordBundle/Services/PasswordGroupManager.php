<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupRepository;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\PasswordBundle\Model\PasswordGroupCollectionModel;
use Main\UserBundle\Entity\User;

class PasswordGroupManager
{
    /**
     * @var PasswordGroupRepository
     */
    protected $passwordGroupRepository;

    /**
     * @var PasswordAccessManager
     */
    protected $passwordAccessManager;

    /**
     * @var PasswordUserGroupAccessManager
     */
    private $passwordUserGroupAccessManager;

    /**
     * @param PasswordGroupRepository        $passwordGroupRepository
     * @param PasswordAccessManager          $passwordAccessManager
     * @param PasswordUserGroupAccessManager $passwordUserGroupAccessManager
     */
    public function __construct(
        PasswordGroupRepository $passwordGroupRepository,
        PasswordAccessManager $passwordAccessManager,
        PasswordUserGroupAccessManager $passwordUserGroupAccessManager)
    {
        $this->passwordGroupRepository = $passwordGroupRepository;
        $this->passwordAccessManager = $passwordAccessManager;
        $this->passwordUserGroupAccessManager = $passwordUserGroupAccessManager;
    }

    /**
     * @param $id
     *
     * @return null|PasswordGroup
     */
    public function getPasswordGroupById($id)
    {
        return $this->passwordGroupRepository->find($id);
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param bool          $flush
     */
    public function createPasswordGroup(PasswordGroup $passwordGroup, $flush = true)
    {
        $this->passwordGroupRepository->save($passwordGroup, $flush);
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param bool          $flush
     */
    public function updatePasswordGroup(PasswordGroup $passwordGroup, $flush = true)
    {
        $this->passwordGroupRepository->save($passwordGroup, $flush);
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param bool          $flush
     */
    public function deletePasswordGroup(PasswordGroup $passwordGroup, $flush = true)
    {
        $this->passwordGroupRepository->remove($passwordGroup, $flush);
    }

    /**
     * @param int  $passwordGroupId
     * @param User $user
     *
     * @return PasswordGroup|null
     */
    public function getPasswordGroupByIdAndUser($passwordGroupId, User $user)
    {
        return $this->passwordGroupRepository->qbPasswordGroupByIdAndUser($passwordGroupId, $user)->getQuery()->getOneOrNullResult();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function findAllPasswordGroupsByUser(User $user)
    {
        $collection = new PasswordGroupCollectionModel();

        $passwordGroupAccesses = $this->passwordAccessManager->findAllPasswordGroupAccessesByUser($user);

        foreach ($passwordGroupAccesses as $passwordGroupAccess) {
            $collection->addPasswordGroup($passwordGroupAccess->getPasswordGroup(), $passwordGroupAccess->getRight());
        }

        $passwordAccesses = $this->passwordAccessManager->findAllPasswordAccessesByUser($user);

        foreach ($passwordAccesses as $passwordAccess) {
            $collection->addPasswordGroup($passwordAccess->getPassword()->getPasswordGroup(), 0);
        }

        foreach ($user->getUserGroups() as $userGroup) {
            $passwordGroupUserGroupAccesses = $this->passwordUserGroupAccessManager->findAllPasswordGroupUserGroupAccessesByUserGroup($userGroup, $user);

            foreach ($passwordGroupUserGroupAccesses as $passwordGroupUserGroupAccess) {
                $collection->addPasswordGroup($passwordGroupUserGroupAccess->getPasswordGroup(), $passwordGroupUserGroupAccess->getRight());
            }

            $passwordUserGroupAccesses = $this->passwordUserGroupAccessManager->findAllPasswordUserGroupAccessesByUserGroup($userGroup, $user);

            foreach ($passwordUserGroupAccesses as $passwordUserGroupAccess) {
                $collection->addPasswordGroup($passwordUserGroupAccess->getPassword()->getPasswordGroup(), 0);
            }
        }

        return $collection->getCurrent();
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     * @param bool          $flush
     */
    public function createPasswordGroupByUser(PasswordGroup $passwordGroup, User $user, $flush = true)
    {
        $access = $this->passwordAccessManager->createPasswordGroupAccessEntity($passwordGroup, $user, AccessRightModel::RIGHT_ADMIN);

        $this->passwordGroupRepository->save($passwordGroup, false);

        $this->passwordAccessManager->updatePasswordGroupAccess($access, $flush);
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return int
     */
    public function countPasswordGroupByParent(PasswordGroup $passwordGroup)
    {
        return $this->passwordGroupRepository->countByParent($passwordGroup);
    }

    /**
     * @param PasswordGroup $checkPasswordGroup
     * @param PasswordGroup $passwordGroup
     *
     * @return bool
     */
    public function isPasswordGroupParentOfPasswordGroup(PasswordGroup $checkPasswordGroup, PasswordGroup $passwordGroup)
    {
        if ($passwordGroup->getId() == $checkPasswordGroup->getId()) {
            return false;
        }

        if ($passwordGroup->getParent() instanceof PasswordGroup) {
            if ($passwordGroup->getParent()->getId() == $checkPasswordGroup->getId()) {
                return true;
            }

            return $this->isPasswordGroupParentOfPasswordGroup($checkPasswordGroup, $passwordGroup->getParent());
        }

        return false;
    }
}
