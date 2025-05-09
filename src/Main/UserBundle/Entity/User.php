<?php

namespace Main\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use JMS\Serializer\Annotation as Serializer;
use Main\LanguageBundle\Entity\Language;
use Main\PasswordBundle\Interfaces\PasswordLoggableReferenceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="users", indexes={
 *     @ORM\Index(name="user_first_name", columns={"first_name"}),
 *     @ORM\Index(name="user_last_name", columns={"last_name"}),
 *     @ORM\Index(name="user_full_name", columns={"full_name"})
 * })
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "user" = "User",
 *     "ldap_user" = "LdapUser"
 * })
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @UniqueEntity(
 *     fields={"username"},
 *     errorPath="username",
 *     message="This username is already in use."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     repositoryMethod="findByUniqueCriteria",
 *     message="This email address is already in use."
 * )
 *
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email",
 *          column=@ORM\Column(
 *              name     = "email",
 *              type     = "string",
 *              length   = 180,
 *              nullable = true,
 *              unique   = false
 *          )
 *      ),
 *     @ORM\AttributeOverride(name="emailCanonical",
 *          column=@ORM\Column(
 *              name     = "email_canonical",
 *              type     = "string",
 *              length   = 180,
 *              nullable = true,
 *              unique   = true
 *          )
 *      )
 * })
 */
class User extends BaseUser implements PasswordLoggableReferenceInterface
{
    const SALUTATION_COMPANY = 1;
    const SALUTATION_MALE = 2;
    const SALUTATION_FEMALE = 3;
    const SALUTATION_UNKNOWN = 4;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Serializer\Expose()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Main\LanguageBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false)
     *
     * @Serializer\Expose()
     *
     * @var Language
     */
    protected $language;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Serializer\Expose()
     * @Serializer\Groups("ROLE_ADMIN")
     *
     * @var bool
     */
    protected $deleted;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     *
     * @Assert\NotBlank(groups={"EditUser"})
     * @Assert\Length(
     *     max = 32,
     *     groups={"EditUser"}
     * )
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     *
     * @Assert\NotBlank(groups={"EditUser"})
     * @Assert\Length(
     *     max = 32,
     *     groups={"EditUser"}
     * )
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\Column(name="full_name", type="string", nullable=true)
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $fullName;

    /**
     * @ORM\Column(name="secret", type="string", nullable=true)
     *
     * @var string
     */
    protected $secret;

    /**
     * @ORM\ManyToMany(targetEntity="Main\UserBundle\Entity\UserGroup", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="user_group_allocations",
     *     joinColumns={@ORM\JoinColumn(name="id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @Serializer\Expose()
     *
     * @var UserGroup[]|ArrayCollection
     */
    protected $userGroups;

    /**
     * @param int $id
     */
    public function __construct($id = null)
    {
        parent::__construct();

        $this->id = $id;
        $this->enabled = true;
        $this->deleted = false;
        $this->userGroups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        $this->updateFullName();
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        $this->updateFullName();
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return null
     */
    public function updateFullName()
    {
        $this->fullName = sprintf('%s %s', $this->firstName, $this->lastName);

        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups("ROLE_ADMIN")
     *
     * @return bool
     */
    public function hasSecret()
    {
        return $this->secret !== null;
    }

    /**
     * @return string|null
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string|null $secret
     */
    public function setSecret($secret = null)
    {
        $this->secret = $secret;
    }

    /**
     * @Serializer\VirtualProperty()
     *
     * @Serializer\Groups({"ROLE_ADMIN"})
     */
    public function getAdmin()
    {
        if (in_array('ROLE_ADMIN', $this->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled && !$this->deleted;
    }

    /**
     * @return ArrayCollection|UserGroup[]
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * @param UserGroup $userGroup
     */
    public function addUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->add($userGroup);
    }

    /**
     * @param UserGroup $userGroup
     */
    public function removeUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->removeElement($userGroup);
    }
}
