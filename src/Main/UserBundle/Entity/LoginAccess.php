<?php

namespace Main\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\LoginAccess as BaseLoginAccess;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="Main\UserBundle\Entity\Repository\LoginAccessRepository")
 * @ORM\Table(name="user_login_accesses")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class LoginAccess extends BaseLoginAccess
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Serializer\Groups({"asdasdasdasd"})
     * @Serializer\Expose()
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

    /**
     * @return string
     */
    public function getToIp()
    {
        if (empty($this->toIp)) {
            return "";
        }

        $ip = $this->toIp;

        if (is_resource($ip)) {
            fseek($ip, 0);
            $ip = stream_get_contents($ip);
        }

        return inet_ntop($ip);
    }

    /**
     * @return string
     */
    public function getFromIp()
    {
        if (empty($this->fromIp)) {
            return "";
        }

        $ip = $this->fromIp;

        if (is_resource($ip)) {
            fseek($ip, 0);
            $ip = stream_get_contents($ip);
        }

        return inet_ntop($ip);
    }
}
