<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Model;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
abstract class InvalidLogin implements InvalidLoginInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $ip;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var \DateTime
     */
    protected $createDate;

    /**
     * @var \DateTime
     */
    protected $lastFailDate;

    public function __construct()
    {
        $this->createDate = new \DateTime();
        $this->lastFailDate = new \DateTime();
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
    public function getIp()
    {
        return inet_ntop($this->ip);
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = inet_pton($ip);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreateDate()
    {
        return \DateTimeImmutable::createFromMutable($this->createDate);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastFailDate()
    {
        return \DateTimeImmutable::createFromMutable($this->lastFailDate);
    }

    /**
     * @param \DateTime $lastFailDate
     */
    public function setLastFailDate(\DateTime $lastFailDate)
    {
        $this->lastFailDate = $lastFailDate;
    }
}
