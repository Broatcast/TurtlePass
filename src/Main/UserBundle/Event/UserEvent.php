<?php

namespace Main\UserBundle\Event;

use FOS\UserBundle\Model\UserInterface;
use Main\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
