<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Configuration\ConfigurationManager;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity\InvalidLoginRepositoryInterface;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLogin;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class InvalidLoginManager
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var InvalidLoginRepositoryInterface
     */
    protected $invalidLoginRepository;

    /**
     * @param Registry             $doctrine
     * @param ConfigurationManager $configurationManager
     *
     * @throws \Exception
     */
    public function __construct(Registry $doctrine, ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->invalidLoginRepository = $doctrine->getRepository($configurationManager->getInvalidLoginClass());

        if (!$this->invalidLoginRepository instanceof InvalidLoginRepositoryInterface) {
            throw new \Exception('InvalidLogin entity repository is not instance of InvalidLoginRepositoryInterface.');
        }
    }

    /**
     * @param string $ipAddress
     *
     * @return null|\UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLoginInterface
     */
    public function getInvalidLoginByIpAddress($ipAddress)
    {
        return $this->invalidLoginRepository->findInvalidLoginByIpAddress($ipAddress);
    }

    /**
     * @param string $ipAddress
     */
    public function addFail($ipAddress)
    {
        $className = $this->configurationManager->getInvalidLoginClass();

        /** @var InvalidLogin $invalidLogin */
        $invalidLogin = new $className();
        $invalidLogin->setIp($ipAddress);
        $invalidLogin->setCount(1);

        $this->invalidLoginRepository->persist($invalidLogin);
    }

    /**
     * @param string $ipAddress
     */
    public function updateFail($ipAddress)
    {
        $invalidLogin = $this->invalidLoginRepository->findInvalidLoginByIpAddress($ipAddress);

        if ($invalidLogin instanceof InvalidLogin) {
            $invalidLogin->setCount($invalidLogin->getCount()+1);

            $this->invalidLoginRepository->persist($invalidLogin);
        } else {
            $this->addFail($ipAddress);
        }
    }
}