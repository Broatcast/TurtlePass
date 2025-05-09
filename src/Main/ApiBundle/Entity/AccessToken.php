<?php

namespace Main\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Main\UserBundle\Entity\User;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\ApiBundle\Entity\AccessTokenRepository")
 * @ORM\Table(name="api_access_token", indexes={
 *     @ORM\Index(name="api_access_token_default", columns={"is_default"}),
 *     @ORM\Index(name="api_access_token_token", columns={"token"})
 * })
 *
 * @Hateoas\Relation("self", href = "expr('/api/tokens/' ~ object.getId())")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Main\ApiBundle\Entity\Client")
     * @ORM\JoinColumn(name="client_id", nullable=false)
     *
     * @var Client
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="Main\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(name="description", type="string", length=64, nullable=true)
     *
     * @Assert\Length(
     *      max = 64
     * )
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $description;

    /**
     * Handelt es sich um einen Token für AngularJS
     *
     * @ORM\Column(name="is_default", type="boolean")
     *
     * @var bool
     */
    protected $default;

    /**
     * Wurde der Token per OAuth angefordert oder per API generiert
     *
     * @ORM\Column(name="is_custom", type="boolean")
     *
     * @var bool
     */
    protected $custom;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->default = false;
        $this->custom = false;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->custom;
    }

    /**
     * @param bool $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
     * @Serializer\VirtualProperty()
     */
    public function getExpire()
    {
        if (is_null($this->expiresAt) || $this->expiresAt == -1) {
            return null;
        }

        $date = new \DateTime();
        $date->setTimestamp($this->expiresAt);

        return $date;
    }
}
