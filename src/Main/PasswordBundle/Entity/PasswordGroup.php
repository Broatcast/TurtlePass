<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordGroupRepository")
 * @ORM\Table(name="password_groups", indexes={
 *     @ORM\Index(name="password_group_name", columns={"name"})
 * })
 *
 * @Serializer\ExclusionPolicy("all")
 */
class PasswordGroup
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
     * @ORM\Column(type="string")
     *
     * @Serializer\Expose()
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="32")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     *
     * @Serializer\Expose()
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $icon;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowPasswordGroupDescription"})
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\PasswordGroup", inversedBy="children")
     * @ORM\JoinColumn(name="password_group_id", referencedColumnName="id", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"ShowParentPasswordGroups"})
     *
     * @var PasswordGroup|null
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Main\PasswordBundle\Entity\PasswordGroup", mappedBy="parent")
     *
     * @var PasswordGroup[]|ArrayCollection
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="Main\PasswordBundle\Entity\PasswordGroupAccess", mappedBy="passwordGroup")
     *
     * @var PasswordGroupAccess[]
     */
    protected $passwordGroupAccesses;

    /**
     * @ORM\OneToMany(targetEntity="Main\PasswordBundle\Entity\PasswordGroupUserGroupAccess", mappedBy="passwordGroup")
     *
     * @var PasswordGroupUserGroupAccess[]
     */
    protected $passwordGroupUserGroupAccess;

    /**
     * @ORM\OneToMany(targetEntity="Main\PasswordBundle\Entity\PasswordGroupSort", mappedBy="passwordGroup")
     *
     * @var PasswordGroupSort[]
     */
    protected $passwordGroupSorting;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        $this->passwordGroupAccesses = new ArrayCollection();
        $this->passwordGroupUserGroupAccess = new ArrayCollection();
        $this->passwordGroupSorting = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

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
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return PasswordGroup
     */
    public function getHeadPasswordGroup()
    {
        if ($this->parent instanceof self) {
            return $this->parent->getHeadPasswordGroup();
        }

        return $this;
    }

    /**
     * @return PasswordGroup|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param PasswordGroup|null $parent
     */
    public function setParent(PasswordGroup $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return PasswordGroupAccess[]
     */
    public function getPasswordGroupAccesses()
    {
        return $this->passwordGroupAccesses;
    }

    /**
     * @return ArrayCollection|PasswordGroup[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @Serializer\VirtualProperty()
     *
     * @Serializer\Groups({"ShowAccess"})
     */
    public function getAccess()
    {
        $right = 0;
        if (count($this->passwordGroupAccesses)) {
            $right = $this->passwordGroupAccesses[0]->getRight();
        }

        if (count($this->passwordGroupUserGroupAccess)) {
            foreach ($this->passwordGroupUserGroupAccess as $passwordGroupUserGroupAccess) {
                if ($passwordGroupUserGroupAccess->getRight() > $right) {
                    $right = $passwordGroupUserGroupAccess->getRight();
                }
            }
        }

        return $right;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getSorting()
    {
        if (count($this->passwordGroupSorting)) {
            return $this->passwordGroupSorting->first()->getSorting();
        }

        return 0;
    }

    /**
     * @return PasswordGroupSort[]
     */
    public function getPasswordGroupSorting()
    {
        return $this->passwordGroupSorting;
    }

    /**
     * @param PasswordGroupSort[] $passwordGroupSorting
     */
    public function setPasswordGroupSorting($passwordGroupSorting)
    {
        $this->passwordGroupSorting = $passwordGroupSorting;
    }
}
