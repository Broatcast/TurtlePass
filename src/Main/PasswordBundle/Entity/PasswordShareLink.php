<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Main\PasswordBundle\Interfaces\PasswordLoggableReferenceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordShareLinkRepository")
 * @ORM\Table(name="password_share_links")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class PasswordShareLink implements PasswordLoggableReferenceInterface
{
    const MODE_READ = 1;
    const MODE_READ_WRITE = 2;

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
     * @ORM\ManyToOne(targetEntity="Password")
     * @ORM\JoinColumn(name="password_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowPassword"})
     *
     * @var Password
     */
    protected $password;

    /**
     * @ORM\Column(type="integer", name="`mode`")
     *
     * @var integer
     */
    protected $mode;

    /**
     * @ORM\Column(type="string", name="token")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(type="datetime", name="create_date")
     *
     * @Serializer\Expose()
     *
     * @var \DateTime
     */
    protected $createDate;

    /**
     * @ORM\Column(type="datetime", name="valid_to", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var \DateTimeInterface
     */
    protected $validTo;

    /**
     * @ORM\Column(type="string", name="recipient", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @Assert\Email()
     *
     * @var string
     */
    protected $recipient;

    /**
     * @ORM\Column(type="integer", name="view_limit", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var int|null
     */
    protected $viewLimit;

    /**
     * @ORM\Column(type="integer", name="view_count", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var int|null
     */
    protected $viewCount;

    /**
     * @param int|null $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->mode = self::MODE_READ;
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
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
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
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param \DateTimeInterface|null $validTo
     */
    public function setValidTo(\DateTimeInterface $validTo = null)
    {
        $this->validTo = $validTo;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return int|null
     */
    public function getViewLimit()
    {
        return $this->viewLimit;
    }

    /**
     * @param int|null $viewLimit
     */
    public function setViewLimit($viewLimit)
    {
        $this->viewLimit = $viewLimit;
    }

    /**
     * @return int|null
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }

    /**
     * @param int|null $viewCount
     */
    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("mode")
     *
     * @return string
     */
    public function getModeAsString()
    {
        switch ($this->getMode()) {
            default:
            case self::MODE_READ:
                return 'read';
            case self::MODE_READ_WRITE:
                return 'read_write';
        }
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("expired")
     *
     * @return bool
     */
    public function getExpired()
    {
        if ($this->getViewLimit() !== null && $this->getViewLimit() <= $this->getViewCount()) {
            return true;
        }

        if ($this->getValidTo() !== null && $this->getValidTo()->getTimestamp() <= time()) {
            return true;
        }

        return false;
    }
}