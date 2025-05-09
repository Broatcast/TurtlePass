<?php

namespace Main\FOSUserRecaptchaProtectionBundle\EventListener;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Services\SettingManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Configuration\ConfigurationManager;

class SettingListener
{
    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @param ConfigurationManager $configurationManager
     * @param SettingManager       $settingManager
     */
    public function __construct(ConfigurationManager $configurationManager, SettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @throws AccessDeniedHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->configurationManager->setRecaptchaSiteKey($this->settingManager->getSetting(Setting::ID_RECAPTCHA_SITE_KEY)->getValueAllowNull());
        $this->configurationManager->setRecaptchaPrivateKey($this->settingManager->getSetting(Setting::ID_RECAPTCHA_PRIVATE_KEY)->getValueAllowNull());
        $this->configurationManager->setAllowOnlyWhitelisted($this->settingManager->getSetting(Setting::ID_ONLY_WHITELISTED)->getValueAllowNull() === '1');
        $this->configurationManager->setRecaptchaNeededFails($this->settingManager->getSetting(Setting::ID_RECAPTCHA_NEEDED_FAILS)->getValue());
    }
}
