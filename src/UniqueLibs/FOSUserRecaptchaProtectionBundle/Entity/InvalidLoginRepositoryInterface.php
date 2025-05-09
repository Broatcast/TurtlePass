<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLoginInterface;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
interface InvalidLoginRepositoryInterface
{
    /**
     * @param InvalidLoginInterface $invalidLogin
     * @param bool                  $flush
     */
    public function persist(InvalidLoginInterface $invalidLogin, $flush = true);

    /**
     * @param InvalidLoginInterface $invalidLogin
     * @param bool                  $flush
     */
    public function remove(InvalidLoginInterface $invalidLogin, $flush = true);

    /**
     * @param string $ipAddress
     *
     * @return InvalidLoginInterface|null
     */
    public function findInvalidLoginByIpAddress($ipAddress);
}
