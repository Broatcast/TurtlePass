<?php

namespace Main\PasswordBundle\Event;

use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Services\PasswordGroupManager;
use Main\UserBundle\Event\UserEvent;
use Main\UserBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\Translator;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var PasswordGroupManager
     */
    private $passwordGroupManager;
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param PasswordGroupManager $passwordGroupManager
     * @param Translator           $translator
     */
    public function __construct(PasswordGroupManager $passwordGroupManager, Translator $translator)
    {
        $this->passwordGroupManager = $passwordGroupManager;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::USER_CREATED => 'onUserCreated',
        ];
    }

    /**
     * @param UserEvent $userEvent
     */
    public function onUserCreated(UserEvent $userEvent)
    {
        $passwordGroup = new PasswordGroup();
        $passwordGroup->setName($this->translator->trans('DEFAULT_GROUP.PERSONAL_PASSWORDS.TITLE'));
        $passwordGroup->setDescription($this->translator->trans('DEFAULT_GROUP.PERSONAL_PASSWORDS.DESCRIPTION'));
        $passwordGroup->setIcon('fa-user');

        $this->passwordGroupManager->createPasswordGroupByUser($passwordGroup, $userEvent->getUser());
    }
}
