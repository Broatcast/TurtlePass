<?php

namespace Main\PasswordBundle\Event;

use Main\PasswordBundle\Entity\PasswordShareLink;
use Symfony\Component\EventDispatcher\Event;

class ShareLinkEvent extends Event
{
    const NAME = 'share_link_created';

    /**
     * @var PasswordShareLink
     */
    protected $passwordShareLink;

    public function __construct(PasswordShareLink $passwordShareLink)
    {
        $this->passwordShareLink = $passwordShareLink;
    }

    /**
     * @return PasswordShareLink
     */
    public function getPasswordShareLink()
    {
        return $this->passwordShareLink;
    }
}