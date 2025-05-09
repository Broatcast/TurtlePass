<?php

namespace Main\FOSUserRecaptchaProtectionBundle\Controller;

use Main\AppBundle\Entity\Setting;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Controller\SecurityController as BaseSecurityController;

class SecurityController extends BaseSecurityController
{
    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderLogin(array $data)
    {
        $settingManager = $this->container->get('main_app.services.setting_manager');

        $registrationEnabled = true;
        $passwordResetEnabled = true;

        if ($settingManager->getSetting(Setting::ID_ENABLE_REGISTRATION)->getValue() == '0') {
            $registrationEnabled = false;
        }

        if ($settingManager->getSetting(Setting::ID_ENABLE_PASSWORD_RESET)->getValue() == '0') {
            $passwordResetEnabled = false;
        }

        return parent::renderLogin(array_merge($data, [
            'registration_enabled' => $registrationEnabled,
            'password_reset_enabled' => $passwordResetEnabled,
        ]));
    }
}
