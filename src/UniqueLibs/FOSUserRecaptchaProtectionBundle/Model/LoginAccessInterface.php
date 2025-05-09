<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Model;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
interface LoginAccessInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getFromIp();

    /**
     * @param int string
     */
    public function setFromIp($ip);

    /**
     * @param int string
     */
    public function setToIp($ip);

    /**
     * @return string
     */
    public function getToIp();

    /**
     * @return boolean
     */
    public function isWhitelist();

    /**
     * @param boolean $whitelist
     */
    public function setWhitelist($whitelist);

    /**
     * @return \DateTimeImmutable
     */
    public function getCreateDate();
}
