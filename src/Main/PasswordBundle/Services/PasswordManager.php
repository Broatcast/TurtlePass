<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Container\LogKeys;
use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordLog;
use Main\PasswordBundle\Entity\PasswordRepository;
use Main\PasswordBundle\Entity\PasswordShareLink;
use Main\UserBundle\Entity\User;

class PasswordManager
{
    /**
     * @var PasswordRepository
     */
    protected $passwordRepository;

    /**
     * @var PasswordLogManager
     */
    protected $passwordLogManager;

    /**
     * @param PasswordRepository $passwordRepository
     * @param PasswordLogManager $passwordLogManager
     */
    public function __construct(PasswordRepository $passwordRepository, PasswordLogManager $passwordLogManager)
    {
        $this->passwordRepository = $passwordRepository;
        $this->passwordLogManager = $passwordLogManager;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllPasswordsByPasswordGroupAndUser(PasswordGroup $passwordGroup, User $user)
    {
        return $this->passwordRepository->qbAllByPasswordGroupAndUser($passwordGroup, $user);
    }

    /**
     * @param Password $password
     * @param User     $user
     * @param bool     $flush
     */
    public function createPasswordByUser(Password $password, User $user, $flush = true)
    {
        $this->passwordRepository->save($password, false);

        $this->passwordLogManager->createPasswordLog($password, LogKeys::KEY_CREATED, $user, $flush);
    }

    /**
     * @param Password $password
     * @param User     $user
     * @param bool     $flush
     */
    public function updatePasswordByUser(Password $password, User $user, $flush = true)
    {
        $this->passwordRepository->save($password, false);

        $this->passwordLogManager->createPasswordLog($password, LogKeys::KEY_UPDATED, $user, $flush);
    }

    /**
     * @param Password $password
     * @param User     $user
     * @param bool     $flush
     */
    public function movePasswordByUser(Password $password, User $user, $flush = true)
    {
        $this->passwordRepository->save($password, false);

        $this->passwordLogManager->createPasswordLog($password, LogKeys::KEY_MOVED, $user, $flush);
    }

    /**
     * @param Password          $password
     * @param PasswordShareLink $passwordShareLink
     * @param bool              $flush
     */
    public function updatePasswordByShareLink(Password $password, PasswordShareLink $passwordShareLink, $flush = true)
    {
        $this->passwordRepository->save($password, false);

        $this->passwordLogManager->createPasswordLog($password, LogKeys::KEY_UPDATED, $passwordShareLink, $flush);
    }

    /**
     * @param Password $password
     * @param bool     $flush
     */
    public function deletePassword(Password $password, $flush = true)
    {
        $this->passwordRepository->remove($password, $flush);
    }

    /**
     * @param int  $passwordId
     * @param User $user
     *
     * @return Password|null
     */
    public function getPasswordByIdAndUser($passwordId, User $user)
    {
        return $this->passwordRepository->qbPasswordByIdAndUser($passwordId, $user)->getQuery()->getOneOrNullResult();
    }

    /**
     * @param User   $user
     * @param string $query
     *
     * @return Password[]
     */
    public function searchPasswordByUserAndQuery(User $user, $query)
    {
        return $this->passwordRepository->qbSearchByUserAndQuery($user, $query)->getQuery()->getResult();
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return bool
     */
    public function showCompletePassword(Password $password, User $user)
    {
        if (!$password->isLogEnabled()) {
            return true;
        }

        $passwordLog = $this->passwordLogManager->getLastPasswordLog($password, $user, LogKeys::KEY_VIEW);

        if ($passwordLog instanceof PasswordLog && $passwordLog->getCreateDate() > new \DateTime('-1 hour')) {
            return true;
        }

        return false;
    }

    /**
     * @param Password $password
     * @param User     $user
     *
     * @return array
     */
    public function getPasswordSerializerGroups(Password $password, User $user)
    {
        $groups = ['ShowAccess'];

        if ($this->showCompletePassword($password, $user)) {
            $groups = array_merge($groups, ['ShowNotice', 'ShowPasswordExtended']);
        }

        return $groups;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getAllByPasswords($offset, $limit)
    {
        return $this->passwordRepository->getAllByPasswords($offset, $limit);
    }
}
