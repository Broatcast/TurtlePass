<?php

namespace Main\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use Doctrine\ORM\Mapping as ORM;
use Main\UserBundle\Entity\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="api_refresh_token")
 */
class RefreshToken extends BaseRefreshToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Main\ApiBundle\Entity\Client")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Client
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="Main\UserBundle\Entity\User")
     *
     * @var User
     */
    protected $user;
}
