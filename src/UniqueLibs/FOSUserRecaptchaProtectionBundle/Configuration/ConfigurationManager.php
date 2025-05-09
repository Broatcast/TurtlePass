<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Configuration;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class ConfigurationManager
{
    /**
     * @var string
     */
    protected $invalidLoginClass;

    /**
     * @var string
     */
    protected $loginAccessClass;

    /**
     * @var string
     */
    protected $recaptchaSiteKey;

    /**
     * @var string
     */
    protected $recaptchaPrivateKey;

    /**
     * @var int
     */
    protected $recaptchaNeededFails;

    /**
     * @var bool
     */
    protected $recaptchaOnPasswordReset;

    /**
     * @var bool
     */
    protected $recaptchaOnRegistration;

    /**
     * @var string
     */
    protected $lockTime;

    /**
     * @var int
     */
    protected $allowedRetries;

    /**
     * @var bool
     */
    protected $allowOnlyWhitelisted;

    /**
     * @param $invalidLoginClass
     * @param $loginAccessClass
     * @param $recaptchaSiteKey
     * @param $recaptchaPrivateKey
     * @param $recaptchaNeededFails
     * @param $recaptchaOnPasswordReset
     * @param $lockTime
     * @param $allowedRetries
     * @param $allowOnlyWhitelisted
     */
    public function __construct(
        $invalidLoginClass,
        $loginAccessClass,
        $recaptchaSiteKey,
        $recaptchaPrivateKey,
        $recaptchaNeededFails,
        $recaptchaOnPasswordReset,
        $recaptchaOnRegistration,
        $lockTime,
        $allowedRetries,
        $allowOnlyWhitelisted
    )
    {
        $this->invalidLoginClass = $invalidLoginClass;
        $this->loginAccessClass = $loginAccessClass;
        $this->recaptchaSiteKey = $recaptchaSiteKey;
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
        $this->recaptchaNeededFails = $recaptchaNeededFails;
        $this->recaptchaOnPasswordReset = $recaptchaOnPasswordReset;
        $this->recaptchaOnRegistration = $recaptchaOnRegistration;
        $this->lockTime = $lockTime;
        $this->allowedRetries = $allowedRetries;
        $this->allowOnlyWhitelisted = $allowOnlyWhitelisted;
    }

    /**
     * @return string
     */
    public function getInvalidLoginClass()
    {
        return $this->invalidLoginClass;
    }

    /**
     * @param string $invalidLoginClass
     */
    public function setInvalidLoginClass($invalidLoginClass)
    {
        $this->invalidLoginClass = $invalidLoginClass;
    }

    /**
     * @return string
     */
    public function getLoginAccessClass()
    {
        return $this->loginAccessClass;
    }

    /**
     * @param string $loginAccessClass
     */
    public function setLoginAccessClass($loginAccessClass)
    {
        $this->loginAccessClass = $loginAccessClass;
    }

    /**
     * @return string
     */
    public function getRecaptchaSiteKey()
    {
        return $this->recaptchaSiteKey;
    }

    /**
     * @param string $recaptchaSiteKey
     */
    public function setRecaptchaSiteKey($recaptchaSiteKey)
    {
        $this->recaptchaSiteKey = $recaptchaSiteKey;
    }

    /**
     * @return string
     */
    public function getRecaptchaPrivateKey()
    {
        return $this->recaptchaPrivateKey;
    }

    /**
     * @param string $recaptchaPrivateKey
     */
    public function setRecaptchaPrivateKey($recaptchaPrivateKey)
    {
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
    }

    /**
     * @return int
     */
    public function getRecaptchaNeededFails()
    {
        return $this->recaptchaNeededFails;
    }

    /**
     * @param int $recaptchaNeededFails
     */
    public function setRecaptchaNeededFails($recaptchaNeededFails)
    {
        $this->recaptchaNeededFails = $recaptchaNeededFails;
    }

    /**
     * @return bool
     */
    public function isRecaptchaOnPasswordReset()
    {
        return $this->recaptchaOnPasswordReset;
    }

    /**
     * @param bool $recaptchaOnPasswordReset
     */
    public function setRecaptchaOnPasswordReset($recaptchaOnPasswordReset)
    {
        $this->recaptchaOnPasswordReset = $recaptchaOnPasswordReset;
    }

    /**
     * @return bool
     */
    public function isRecaptchaOnRegistration()
    {
        return $this->recaptchaOnRegistration;
    }

    /**
     * @param bool $recaptchaOnRegistration
     */
    public function setRecaptchaOnRegistration($recaptchaOnRegistration)
    {
        $this->recaptchaOnRegistration = $recaptchaOnRegistration;
    }

    /**
     * @return string
     */
    public function getLockTime()
    {
        return $this->lockTime;
    }

    /**
     * @param string $lockTime
     */
    public function setLockTime($lockTime)
    {
        $this->lockTime = $lockTime;
    }

    /**
     * @return int
     */
    public function getAllowedRetries()
    {
        return $this->allowedRetries;
    }

    /**
     * @param int $allowedRetries
     */
    public function setAllowedRetries($allowedRetries)
    {
        $this->allowedRetries = $allowedRetries;
    }

    /**
     * @return bool
     */
    public function isAllowOnlyWhitelisted()
    {
        return $this->allowOnlyWhitelisted;
    }

    /**
     * @param bool $allowOnlyWhitelisted
     */
    public function setAllowOnlyWhitelisted($allowOnlyWhitelisted)
    {
        $this->allowOnlyWhitelisted = $allowOnlyWhitelisted;
    }
}