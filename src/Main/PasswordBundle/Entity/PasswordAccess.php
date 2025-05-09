<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Main\PasswordBundle\Model\AccessRightModel;
use Main\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordAccessRepository")
 * @ORM\Table(name="password_accesses", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="password_access_uqe",columns={"password_id", "user_id"})}
 * )
 *
 * @UniqueEntity(
 *     fields={"password", "user"},
 *     errorPath="user",
 *     message="User has already password right."
 * )
 *
 * @Serializer\ExclusionPolicy("all")
 */
class PasswordAccess
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
     * @Assert\NotBlank()
     *
     * @Serializer\Expose()
     *
     * @var int
     */
    protected $right;

    /**
     * @ORM\ManyToOne(targetEntity="Main\PasswordBundle\Entity\Password", inversedBy="passwordAccesses")
     * @ORM\JoinColumn(name="password_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank()
     *
     * @var Password
     */
    protected $password;

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
