<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordLog;
use Main\PasswordBundle\Entity\PasswordLogRepository;
use Main\PasswordBundle\Entity\PasswordShareLink;
use Main\PasswordBundle\Interfaces\PasswordLoggableReferenceInterface;
use Main\UserBundle\Entity\User;

class PasswordLogManager
{
    /**
     * @var PasswordLogRepository
     */
    protected $passwordLogRepository;

    /**
     * @param PasswordLogRepository $passwordLogRepository
     */
    public function __construct(PasswordLogRepository $passwordLogRepository)
    {
        $this->passwordLogRepository = $passwordLogRepository;
    }

    /**
     * @param Password                                $password
     * @param string                                  $key
     * @param PasswordLoggableReferenceInterface|null $loggedReference
     * @param bool                                    $flush
     *
     * @return PasswordLog
     */
    public function createPasswordLog(Password $password, $key, PasswordLoggableReferenceInterface $loggedReference = null, $flush = true)
    {
        $passwordLog = new PasswordLog();
        $passwordLog->setPassword($password);
        $passwordLog->setKey($key);

        if ($loggedReference instanceof User) {
            $passwordLog->setUser($loggedReference);
        } else if ($loggedReference instanceof PasswordShareLink) {
            $passwordLog->setShareLink($loggedReference);
        }

        $this->passwordLogRepository->save($passwordLog, $flush);

        return $passwordLog;
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllPasswordLogsByPassword(Password $password)
    {
        return $this->passwordLogRepository->qbAllByPassword($password);
    }

    /**
     * @param Password  $password
     * @param User|null $user
     * @param null      $key
     *
     * @return PasswordLog|null
     */
    public function getLastPasswordLog(Password $password, User $user = null, $key = null)
    {
        return $this->passwordLogRepository->findLastPasswordLog($password, $user, $key);
    }
}