<?php

namespace Main\PasswordBundle\Entity\PasswordType;

use Main\PasswordBundle\Entity\Password;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordRepository")
 * @ORM\Table(name="password_servers")
 */
class ServerPassword extends Password
{
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
    protected $serverHost;

    /**
     * @ORM\Column(type="string", name="port", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\SerializedName("port")
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $serverPort;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->icon = 'fa-server';
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->serverHost;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->serverHost = $host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->serverPort;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->serverPort = $port;
    }
}
