<?php

namespace Main\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\UserBundle\Entity\Repository\UserGroupRepository")
 * @ORM\Table(name="user_groups")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class UserGroup
{
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
     * @ORM\Column(name="name", type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = 32
     * )
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Main\UserBundle\Entity\User", mappedBy="userGroups")
     *
     * @var ArrayCollection|User[]
     */
    protected $users;

    /**
     * @param int $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;

        $this->users = new ArrayCollection();
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
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }
}
