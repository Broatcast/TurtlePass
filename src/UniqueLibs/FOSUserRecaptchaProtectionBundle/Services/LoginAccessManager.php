<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Configuration\ConfigurationManager;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity\InvalidLoginRepositoryInterface;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity\LoginAccessRepositoryInterface;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLogin;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\LoginAccess;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\LoginAccessInterface;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class LoginAccessManager
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var LoginAccessRepositoryInterface
     */
    protected $loginAccessRepository;

    /**
     * @param Registry             $doctrine
     * @param ConfigurationManager $configurationManager
     *
     * @throws \Exception
     */
    public function __construct(Registry $doctrine, ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->loginAccessRepository = $doctrine->getRepository($configurationManager->getLoginAccessClass());

        if (!$this->loginAccessRepository instanceof LoginAccessRepositoryInterface) {
            throw new \Exception('LoginAccess entity repository is not instance of LoginAccessRepositoryInterface.');
        }
    }

    /**
     * @param string $ipAddress
     * @param bool   $whitelist
     *
     * @return null|\UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\LoginAccessInterface
     */
    public function getInvalidLoginByIpAddress($ipAddress, $whitelist = false)
    {
        return $this->loginAccessRepository->findLoginAccessByIpAddress($ipAddress, $whitelist);
    }

    /**
     * @param string $ipAddress
     *
     * @return bool
     */
    public function isIpAddressBlacklisted($ipAddress)
    {
        return ($this->getInvalidLoginByIpAddress($ipAddress, false) instanceof LoginAccess);
    }

    /**
     * @param string $ipAddress
     *
     * @return bool
     */
    public function isIpAddressWhitelisted($ipAddress)
    {
        return ($this->getInvalidLoginByIpAddress($ipAddress, true) instanceof LoginAccess);
    }

    /**
     * @return LoginAccessInterface
     */
    public function createLoginAccessEntity()
    {
        $className = $this->configurationManager->getLoginAccessClass();

        /** @var LoginAccessInterface $loginAccess */
        $loginAccess = new $className();

        return $loginAccess;
    }
}