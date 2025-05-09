<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Model;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
interface InvalidLoginInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return int
     */
    public function getIp();

    /**
     * @param int $ip
     */
    public function setIp($ip);

    /**
     * @return int
     */
    public function getCount();

    /**
     * @param int $count
     */
    public function setCount($count);

    /**
     * @return \DateTimeImmutable
     */
    public function getCreateDate();

    /**
     * @return \DateTimeImmutable
     */
    public function getLastFailDate();

    /**
     * @param \DateTime $lastFailDate
     */
    public function setLastFailDate(\DateTime $lastFailDate);
}
