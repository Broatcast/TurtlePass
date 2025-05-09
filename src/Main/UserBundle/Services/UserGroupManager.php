<?php

namespace Main\UserBundle\Services;

use Main\UserBundle\Entity\Repository\UserGroupRepository;
use Main\UserBundle\Entity\UserGroup;

class UserGroupManager
{
    /**
     * @var UserGroupRepository
     */
    protected $userGroupRepository;

    /**
     * @param UserGroupRepository $userGroupRepository
     */
    public function __construct(UserGroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAll()
    {
        return $this->userGroupRepository->qbAll();
    }

    /**
     * @param UserGroup $userGroup
     * @param bool      $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(UserGroup $userGroup, $flush = true)
    {
        $this->userGroupRepository->save($userGroup, $flush);
    }

    /**
     * @param UserGroup $userGroup
     * @param bool      $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(UserGroup $userGroup, $flush = true)
    {
        $this->userGroupRepository->save($userGroup, $flush);
    }

    /**
     * @param UserGroup $userGroup
     * @param bool      $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(UserGroup $userGroup, $flush = true)
    {
        $this->userGroupRepository->remove($userGroup, $flush);
    }
}
