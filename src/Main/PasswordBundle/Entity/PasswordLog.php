<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Main\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordLogRepository", readOnly=true)
 * @ORM\Table(name="password_logs", indexes={
 *     @ORM\Index(name="password_log_key", columns={"log_key"}),
 *     @ORM\Index(name="password_log_create_date", columns={"create_date"}),
 *     @ORM\Index(name="password_log_key_create_date", columns={"log_key", "create_date"})
 * })
 *
 * @Serializer\ExclusionPolicy("all")
 */
class PasswordLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Serializer\Expose()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\Password")
     * @ORM\JoinColumn(name="password_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowPassword"})
     *
     * @var Password
     */
    protected $password;

    /**
     * @ORM\ManyToOne(targetEntity="Main\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowUser"})
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\PasswordShareLink")
     * @ORM\JoinColumn(name="share_link_id", referencedColumnName="id", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowUser"})
     *
     * @var PasswordShareLink
     */
    protected $shareLink;

    /**
     * @ORM\Column(type="string", name="log_key")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $key;

    /**
     * @ORM\Column(type="datetime", name="create_date")
     *
     * @Serializer\Expose()
     *
     * @var \DateTime
     */
    protected $createDate;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        $this->createDate = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param Password $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return PasswordShareLink
     */
    public function getShareLink()
    {
        return $this->shareLink;
    }

    /**
     * @param PasswordShareLink $shareLink
     */
    public function setShareLink(PasswordShareLink $shareLink = null)
    {
        $this->shareLink = $shareLink;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate(\DateTime $createDate)
    {
        $this->createDate = $createDate;
    }
}
