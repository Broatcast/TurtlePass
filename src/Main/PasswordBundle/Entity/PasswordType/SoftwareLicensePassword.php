<?php

namespace Main\PasswordBundle\Entity\PasswordType;

use Main\PasswordBundle\Entity\Password;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordRepository")
 * @ORM\Table(name="password_software_licenses")
 */
class SoftwareLicensePassword extends Password
{
    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Length(max="255")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $version;

    /**
     * @ORM\Column(name="license_key", type="string", nullable=true)
     *
     * @Assert\Length(max="255")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $licenseKey;

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     */
    public function setLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;
    }
}