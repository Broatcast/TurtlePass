<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Model;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
abstract class LoginAccess implements LoginAccessInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $fromIp;

    /**
     * @var mixed
     */
    protected $toIp;

    /**
     * @var bool
     */
    protected $whitelist;

    /**
     * @var \DateTime
     */
    protected $createDate;

    /**
     * @var \DateTime|null
     */
    protected $expireDate;

    public function __construct()
    {
        $this->createDate = new \DateTime();
        $this->expireDate = null;
        $this->whitelist = false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFromIp()
    {
        return inet_ntop($this->fromIp);
    }

    /**
     * @param int string
     */
    public function setFromIp($ip)
    {
        $this->fromIp = inet_pton($ip);
    }

    /**
     * @param int string
     */
    public function setToIp($ip)
    {
        $this->toIp = inet_pton($ip);
    }

    /**
     * @return string
     */
    public function getToIp()
    {
        return inet_ntop($this->toIp);
    }

    /**
     * @return boolean
     */
    public function isWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * @param boolean $whitelist
     */
    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreateDate()
    {
        return \DateTimeImmutable::createFromMutable($this->createDate);
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getExpireDate()
    {
        if ($this->expireDate === null) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable($this->expireDate);
    }

    /**
     * @param \DateTime|null $expireDate
     */
    public function setExpireDate(\DateTime $expireDate = null)
    {
        $this->expireDate = $expireDate;
    }
}
