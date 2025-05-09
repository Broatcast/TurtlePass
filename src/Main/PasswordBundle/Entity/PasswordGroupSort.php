<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Main\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordGroupSortRepository")
 * @ORM\Table(name="password_group_sorting", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="password_group_sort_uqe",columns={"password_group_id", "user_id"})}
 * )
 *
 * @UniqueEntity(
 *     fields={"passwordGroup", "user"},
 *     errorPath="user",
 *     message="User has already sorting."
 * )
 *
 * @Serializer\ExclusionPolicy("all")
 */
class PasswordGroupSort
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
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\PasswordGroup", inversedBy="passwordGroupSorting")
     * @ORM\JoinColumn(name="password_group_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var PasswordGroup
     */
    protected $passwordGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Main\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Expose()
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type="integer", name="sorting", nullable=false, options={"unsigned"=true})
     *
     * @Serializer\Expose()
     *
     * @Assert\NotBlank()
     *
     * @var int
     */
    protected $sorting;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
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
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param int $sorting
     *
     * @throws \Exception
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
