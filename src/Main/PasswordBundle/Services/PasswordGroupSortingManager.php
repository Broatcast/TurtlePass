<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupRepository;
use Main\PasswordBundle\Entity\PasswordGroupSortRepository;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\PasswordBundle\Model\PasswordGroupCollectionModel;
use Main\UserBundle\Entity\User;

class PasswordGroupSortingManager
{
    /**
     * @var PasswordGroupSortRepository
     */
    protected $passwordGroupSortRepository;

    public function __construct(PasswordGroupSortRepository $passwordGroupSortRepository)
    {
        $this->passwordGroupSortRepository = $passwordGroupSortRepository;
    }

    /**
     * @param User $user
     */
    public function clearUserSorting(User $user)
    {
        $this->passwordGroupSortRepository->qbClearUserSorting($user)->getQuery()->execute();
    }

    /**
     * @param User  $user
     * @param array $sorting
     *
     * @throws \Exception
     */
    public function saveUserSorting(User $user, array $sorting)
    {
        $this->clearUserSorting($user);

        $inserts = $this->buildInserts($user, $sorting);

        $this->passwordGroupSortRepository->setSorting($inserts);
    }

    /**
     * @param User  $user
     * @param array $sorting
     *
     * @return array
     */
    protected function buildInserts(User $user, array $sorting)
    {
        $inserts = [];

        foreach ($sorting as $item) {
            $inserts[] = [
                'user_id' => (int) $user->getId(),
                'password_group_id' => (int) $item['password_group_id'],
                'sorting' => (int) $item['sorting'],
            ];
        }

        return $inserts;
    }
}
