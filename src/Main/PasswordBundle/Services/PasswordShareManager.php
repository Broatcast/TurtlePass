<?php

namespace Main\PasswordBundle\Services;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordShareLink;
use Main\PasswordBundle\Entity\PasswordShareLinkRepository;
use Main\PasswordBundle\Event\ShareLinkEvent;
use Main\PasswordBundle\Model\PasswordShareLinkModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PasswordShareManager
{
    /**
     * @var PasswordShareLinkRepository
     */
    protected $passwordShareLinkRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param PasswordShareLinkRepository $passwordShareLinkRepository
     * @param EventDispatcherInterface    $eventDispatcher
     */
    public function __construct(PasswordShareLinkRepository $passwordShareLinkRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->passwordShareLinkRepository = $passwordShareLinkRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Password               $password
     * @param PasswordShareLinkModel $passwordShareLinkModel
     * @param int                    $mode
     *
     * @return PasswordShareLink
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createShareLink(Password $password, PasswordShareLinkModel $passwordShareLinkModel, $mode = PasswordShareLink::MODE_READ)
    {
        $passwordShareLink = new PasswordShareLink();
        $passwordShareLink->setToken($this->generateKey());
        $passwordShareLink->setPassword($password);
        $passwordShareLink->setRecipient($passwordShareLinkModel->getRecipient());
        $passwordShareLink->setValidTo($passwordShareLinkModel->getValidTo());
        $passwordShareLink->setViewLimit($passwordShareLinkModel->getViewLimit());
        $passwordShareLink->setMode($mode);

        $this->passwordShareLinkRepository->save($passwordShareLink);

        $this->eventDispatcher->dispatch(
            ShareLinkEvent::NAME,
            new ShareLinkEvent($passwordShareLink)
        );

        return $passwordShareLink;
    }

    /**
     * @param PasswordShareLink $passwordShareLink
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function increaseView(PasswordShareLink $passwordShareLink)
    {
        $passwordShareLink->setViewCount($passwordShareLink->getViewCount()+1);

        $this->passwordShareLinkRepository->save($passwordShareLink);
    }

    /**
     * @param PasswordShareLink $passwordShareLink
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteShareLink(PasswordShareLink $passwordShareLink)
    {
        $passwordShareLink->setValidTo(new \DateTime());

        $this->passwordShareLinkRepository->save($passwordShareLink);
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPassword(Password $password)
    {
        return $this->passwordShareLinkRepository->qbShareLinkByPassword($password);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getActiveShareLinkById($id)
    {
        return $this->passwordShareLinkRepository->findActiveShareLinkById($id);
    }

    /**
     * @param PasswordShareLink $passwordShareLink
     * @param string            $token
     *
     * @return bool
     * @throws \Exception
     */
    public function validateAccess(PasswordShareLink $passwordShareLink, $token)
    {
        $now = new \DateTime();

        if ($passwordShareLink->getValidTo() !== null && $passwordShareLink->getValidTo()->getTimestamp() <= $now->getTimestamp()) {
            return false;
        }

        if ($token !== $passwordShareLink->getToken()) {
            return false;
        }

        if ($passwordShareLink->getViewLimit() !== null && $passwordShareLink->getViewLimit() <= $passwordShareLink->getViewCount()) {
            return false;
        }

        return true;
    }

    /**
     * @param PasswordShareLink $passwordShareLink
     * @param                   $token
     *
     * @return bool
     * @throws \Exception
     */
    public function validateWriteAccess(PasswordShareLink $passwordShareLink, $token)
    {
        if (!$this->validateAccess($passwordShareLink, $token)) {
            return false;
        }

        if ($passwordShareLink->getMode() !== PasswordShareLink::MODE_READ_WRITE) {
            return false;
        }

        return true;
    }

    /**
     * @param int $length
     *
     * @return bool|string
     */
    public function generateKey($length = 128)
    {
        $available = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789~!$%^*(){}[],.';

        return substr(str_shuffle(str_repeat($available, mt_rand(1,10))),1, $length);
    }
}