<?php

namespace Main\UserBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Main\LanguageBundle\Services\LanguageManager;
use Main\UserBundle\Entity\User;
use Main\UserBundle\Entity\UserRepository;
use Main\UserBundle\Event\UserEvent;
use Main\UserBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

class UserManager extends BaseUserManager
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var LanguageManager
     */
    protected $languageManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
     * @param ObjectManager            $om
     * @param                          $class
     * @param UserRepository           $userRepository
     * @param LanguageManager          $languageManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class, UserRepository $userRepository, LanguageManager $languageManager, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);

        $this->userRepository = $userRepository;
        $this->languageManager = $languageManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllNotDeletedUsers()
    {
        return $this->userRepository->qbAllNotDeleted();
    }

    /**
     * @return UserInterface
     */
    public function createUser()
    {
        $user = parent::createUser();

        if ($user instanceof User) {
            $user->setLanguage($this->languageManager->getDefaultLanguage());
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     */
    public function createGivenUser(UserInterface $user)
    {
        $this->updateUser($user);

        $this->eventDispatcher->dispatch(
            Events::USER_CREATED,
            new UserEvent($user)
        );
    }

    /**
     * @param string $username
     *
     * @return UserInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserByUsername($username)
    {
        return $this->userRepository->findUserLocalUserByUsername($this->canonicalize($username));
    }

    public function findAllUserByUsername($username)
    {
        return $this->userRepository->findOneBy(['usernameCanonical' => $this->canonicalize($username)]);
    }

    /**
     * @param UserInterface $user
     */
    public function updateCanonicalFields(UserInterface $user)
    {
        $user->setUsernameCanonical($this->canonicalize($user->getUsername()));
        $emailCanonical = $this->canonicalize($user->getEmail());
        $user->setEmailCanonical($emailCanonical ?: null);
    }

    /**
     * {@inheritdoc}
     */
    public function canonicalize($string)
    {
        if (null === $string) {
            return;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }
}
