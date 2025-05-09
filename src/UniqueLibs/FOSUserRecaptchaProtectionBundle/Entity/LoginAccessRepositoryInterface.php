<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\LoginAccessInterface;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
interface LoginAccessRepositoryInterface
{
    /**
     * @param LoginAccessInterface $loginAccess
     * @param bool                 $flush
     */
    public function persist(LoginAccessInterface $loginAccess, $flush = true);

    /**
     * @param LoginAccessInterface $loginAccess
     * @param bool                 $flush
     */
    public function remove(LoginAccessInterface $loginAccess, $flush = true);

    /**
     * @param string $ipAddress
     * @param bool   $whitelist
     *
     * @return LoginAccessInterface|null
     */
    public function findLoginAccessByIpAddress($ipAddress, $whitelist = false);
}
