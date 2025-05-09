<?php

namespace Main\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FR3D\LdapBundle\Model\LdapUserInterface;

/**
 * @ORM\Entity(repositoryClass="Main\UserBundle\Entity\UserRepository")
 */
class LdapUser extends User implements LdapUserInterface
{
    /**
     * @ORM\Column(name="dn", type="string", nullable=true)
     *
     * @var null|string
     */
    protected $dn;

    /**
     * @return string|null
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * @param string|null $dn
     */
    public function setDn($dn)
    {
        $this->dn = $dn;
    }

    /**
     * prevent null errors in hash-functions
     *
     * @return string
     */
    public function getSalt()
    {
        return (string) parent::getSalt();
    }

    /**
     * prevent null errors in hash-functions
     *
     * @return string
     */
    public function getPassword()
    {
        return (string)  parent::getPassword();
    }
}
