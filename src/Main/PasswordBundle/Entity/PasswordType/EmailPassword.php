<?php

namespace Main\PasswordBundle\Entity\PasswordType;

use Main\PasswordBundle\Entity\Password;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordRepository")
 * @ORM\Table(name="password_emails")
 */
class EmailPassword extends Password
{
    /**
     * @ORM\Column(name="email_type", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $emailType;

    /**
     * @ORM\Column(type="string", name="host", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\SerializedName("host")
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $mailHost;

    /**
     * @ORM\Column(type="string", name="port", nullable=TRUE)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\SerializedName("port")
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $mailPort;

    /**
     * @ORM\Column(name="auth_method", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $authMethod;

    /**
     * @ORM\Column(name="smtp_host", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $smtpHost;

    /**
     * @ORM\Column(name="smtp_port", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $smtpPort;

    /**
     * @ORM\Column(name="smtp_auth_method", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $smtpAuthMethod;

    /**
     * @ORM\Column(name="smtp_username", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $smtpUsername;

    /**
     * @ORM\Column(name="smtp_password", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $smtpPassword;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->icon = 'fa-envelope';
    }

    /**
     * @return string
     */
    public function getEmailType()
    {
        return $this->emailType;
    }

    /**
     * @param string $emailType
     */
    public function setEmailType($emailType)
    {
        $this->emailType = $emailType;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->mailHost;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->mailHost = $host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->mailPort;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->mailPort = $port;
    }

    /**
     * @return string
     */
    public function getAuthMethod()
    {
        return $this->authMethod;
    }

    /**
     * @param string $authMethod
     */
    public function setAuthMethod($authMethod)
    {
        $this->authMethod = $authMethod;
    }

    /**
     * @return string
     */
    public function getSmtpPort()
    {
        return $this->smtpPort;
    }

    /**
     * @param string $smtpPort
     */
    public function setSmtpPort($smtpPort)
    {
        $this->smtpPort = $smtpPort;
    }

    /**
     * @return string
     */
    public function getSmtpAuthMethod()
    {
        return $this->smtpAuthMethod;
    }

    /**
     * @param string $smtpAuthMethod
     */
    public function setSmtpAuthMethod($smtpAuthMethod)
    {
        $this->smtpAuthMethod = $smtpAuthMethod;
    }

    /**
     * @return string
     */
    public function getSmtpHost()
    {
        return $this->smtpHost;
    }

    /**
     * @param string $smtpHost
     */
    public function setSmtpHost($smtpHost)
    {
        $this->smtpHost = $smtpHost;
    }

    /**
     * @return string
     */
    public function getSmtpUsername()
    {
        return $this->smtpUsername;
    }

    /**
     * @param string $smtpUsername
     */
    public function setSmtpUsername($smtpUsername)
    {
        $this->smtpUsername = $smtpUsername;
    }

    /**
     * @return string
     */
    public function getSmtpPassword()
    {
        return $this->smtpPassword;
    }

    /**
     * @param string $smtpPassword
     */
    public function setSmtpPassword($smtpPassword)
    {
        $this->smtpPassword = $smtpPassword;
    }
}