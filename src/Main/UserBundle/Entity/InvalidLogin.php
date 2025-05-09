<?php

namespace Main\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLogin as BaseInvalidLogin;

/**
 * @ORM\Entity(repositoryClass="Main\UserBundle\Entity\Repository\InvalidLoginRepository")
 * @ORM\Table(name="user_invalid_logins")
 */
class InvalidLogin extends BaseInvalidLogin
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
