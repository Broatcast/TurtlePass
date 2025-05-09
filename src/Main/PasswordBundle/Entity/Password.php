<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordRepository")
 * @ORM\Table(name="passwords", indexes={
 *     @ORM\Index(name="password_name_canonical", columns={"name_canonical"}),
 *     @ORM\Index(name="password_username_canonical", columns={"username_canonical"}),
 *     @ORM\Index(name="password_url", columns={"url"}),
 *     @ORM\Index(name="password_search", columns={"name_canonical", "username_canonical", "url"})
 * })
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="password_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "plain" = "Password",
 *     "bankaccount" = "Main\PasswordBundle\Entity\PasswordType\BankAccountPassword",
 *     "email" = "Main\PasswordBundle\Entity\PasswordType\EmailPassword",
 *     "server" = "Main\PasswordBundle\Entity\PasswordType\ServerPassword",
 *     "credit_card" = "Main\PasswordBundle\Entity\PasswordType\CreditCardPassword",
 *     "software_license" = "Main\PasswordBundle\Entity\PasswordType\SoftwareLicensePassword"
 * })
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Password
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
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\PasswordGroup")
     * @ORM\JoinColumn(name="password_group_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowPasswordGroup"})
     *
     * @var PasswordGroup
     */
    protected $passwordGroup;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="name_canonical")
     *
     * @var string
     */
    protected $nameCanonical;

    /**
     * @ORM\Column(type="string", options={"default": "fa-key"})
     *
     * @Serializer\Expose()
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $icon;

    /**
     * @ORM\Column(type="boolean", name="is_log_enabled", options={"default": 0})
     *
     * @Serializer\Expose()
     *
     * @var boolean
     */
    protected $logEnabled;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Length(max="255")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Length(max="255")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string", name="username_canonical", nullable=true)
     *
     * @var string
     */
    protected $usernameCanonical;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\Length(max="255")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime", name="create_date")
     *
     * @Serializer\Expose()
     *
     * @var \DateTime
     */
    protected $createDate;

    /**
     * @ORM\Column(type="datetime", name="last_update_date", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var \DateTime
     */
    protected $lastUpdateDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max="1024")
     *
     * @Encrypted()
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowNotice"})
     *
     * @var string
     */
    protected $notice;

    /**
     * @ORM\Column(name="custom_fields", type="json_array", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var array
     */
    protected $customFields;

    /**
     * @ORM\OneToMany(targetEntity="Main\PasswordBundle\Entity\PasswordAccess", mappedBy="password")
     *
     * @var PasswordAccess[]
     */
    protected $passwordAccesses;

    /**
     * @ORM\OneToMany(targetEntity="Main\PasswordBundle\Entity\PasswordUserGroupAccess", mappedBy="password")
     *
     * @var PasswordUserGroupAccess[]
     */
    protected $passwordUserGroupAccesses;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        $this->icon = 'fa-key';
        $this->logEnabled = false;
        $this->createDate = new \DateTime();
        $this->passwordAccesses = new ArrayCollection();
        $this->passwordUserGroupAccesses = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return PasswordGroup
     */
    public function getPasswordGroup()
    {
        return $this->passwordGroup;
    }

    /**
     * @param PasswordGroup $passwordGroup
     */
    public function setPasswordGroup(PasswordGroup $passwordGroup)
    {
        $this->passwordGroup = $passwordGroup;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->nameCanonical = strtolower($name);
    }

    /**
     * @return string
     */
    public function getNameCanonical()
    {
        return $this->nameCanonical;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return bool
     */
    public function isLogEnabled()
    {
        return $this->logEnabled ? true : false;
    }

    /**
     * @param bool $logEnabled
     */
    public function setLogEnabled($logEnabled)
    {
        $this->logEnabled = $logEnabled ? true : false;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = strtolower($url);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
        $this->usernameCanonical = strtolower($username);
    }

    /**
     * @return string
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getNotice()
    {
        return $this->notice;
    }

    /**
     * @param string $notice
     */
    public function setNotice($notice)
    {
        $this->notice = $notice;
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
     * @return \DateTime
     */
    public function getLastUpdateDate()
    {
        return $this->lastUpdateDate;
    }

    /**
     * @param \DateTime $lastUpdateDate
     */
    public function setLastUpdateDate(\DateTime $lastUpdateDate)
    {
        $this->lastUpdateDate = $lastUpdateDate;
    }

    /**
     * @return PasswordAccess[]
     */
    public function getPasswordAccesses()
    {
        return $this->passwordAccesses;
    }

    /**
     * @return array
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param array $customFields
     */
    public function setCustomFields(array $customFields)
    {
        $this->customFields = $customFields;
    }

    /**
     * @Serializer\VirtualProperty()
     *
     * @Serializer\Groups({"ShowAccess"})
     *
     * @return int
     */
    public function getAccess()
    {
        $right = 0;
        if (count($this->passwordAccesses)) {
            $right = $this->passwordAccesses[0]->getRight();
        }

        if (count($this->passwordUserGroupAccesses)) {
            foreach ($this->passwordUserGroupAccesses as $passwordUserGroupAccess) {
                if ($passwordUserGroupAccess->getRight() > $right) {
                    $right = $passwordUserGroupAccess->getRight();
                }
            }
        }

        return $right;
    }

    /**
     * @Serializer\VirtualProperty()
     *
     * @Serializer\Groups({"ShowPasswordExtended"})
     *
     * @return boolean
     */
    public function getComplete()
    {
        return true;
    }
}
