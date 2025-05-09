<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\UserBundle\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordGroupUserGroupAccessRepository")
 * @ORM\Table(name="password_group_user_group_accesses", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="password_group_user_group_access_uqe",columns={"password_group_id", "user_group_id"})}
 * )
 *
 * @UniqueEntity(
 *     fields={"passwordGroup", "userGroup"},
 *     errorPath="userGroup",
 *     message="User group has already password group right."
 * )
 *
 * @Serializer\ExclusionPolicy("all")
 */
class PasswordGroupUserGroupAccess
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
     * @ORM\Column(type="smallint", name="access_right", nullable=false, options={"unsigned"=true})
     *
     * @Serializer\Expose()
     *
     * @Assert\NotBlank()
     *
     * @var int
     */
    protected $right;

    /**
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\PasswordGroup", inversedBy="passwordGroupUserGroupAccess")
     * @ORM\JoinColumn(name="password_group_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var PasswordGroup
     */
    protected $passwordGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Main\UserBundle\Entity\UserGroup")
     * @ORM\JoinColumn(name="user_group_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Expose()
     *
     * @var UserGroup
     */
    protected $userGroup;

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
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param int $right
     *
     * @throws \Exception
     */
    public function setRight($right)
    {
        if (!AccessRightModel::isRight($right)) {
            throw new \Exception('Invalid right');
        }

        $this->right = $right;
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
     * @return UserGroup
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @param UserGroup $userGroup
     */
    public function setUserGroup(UserGroup $userGroup)
    {
        $this->userGroup = $userGroup;
    }
}
