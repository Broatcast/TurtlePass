<?php

namespace Main\AppBundle\Services;

use Main\AppBundle\Entity\Setting;
use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerTransportFactory;

class DatabaseMailer
{
    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @var \Swift_Events_SimpleEventDispatcher
     */
    protected $swift_Events_SimpleEventDispatcher;

    /**
     * @param SettingManager  $settingManager
     */
    public function __construct(SettingManager $settingManager, \Swift_Events_SimpleEventDispatcher $swift_Events_SimpleEventDispatcher)
    {
        $this->settingManager = $settingManager;
        $this->swift_Events_SimpleEventDispatcher = $swift_Events_SimpleEventDispatcher;
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        $transport = SwiftmailerTransportFactory::createTransport([
            'transport' => $this->settingManager->getSetting(Setting::ID_MAIL_TRANSPORT)->getValue(),
            'username' => $this->settingManager->getSetting(Setting::ID_MAIL_USERNAME)->getValue(),
            'password' => $this->settingManager->getSetting(Setting::ID_MAIL_PASSWORD)->getValue(),
            'host' => $this->settingManager->getSetting(Setting::ID_MAIL_HOST)->getValue(),
            'port' => $this->settingManager->getSetting(Setting::ID_MAIL_PORT)->getValue(),
            'encryption' => $this->settingManager->getSetting(Setting::ID_MAIL_ENCRYPTION)->getValueAllowNull(),
            'auth_mode' => $this->settingManager->getSetting(Setting::ID_MAIL_AUTH_MODE)->getValueAllowNull(),
        ], null, $this->swift_Events_SimpleEventDispatcher);

        $mailer = new \Swift_Mailer($transport);
        
        return $mailer;
    }
}
