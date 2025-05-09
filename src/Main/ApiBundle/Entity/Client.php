<?php

namespace Main\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Main\ApiBundle\Entity\ClientRepository")
 * @ORM\Table(name="api_client")
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        parent::__construct();

        $this->id = $id;
    }
}
