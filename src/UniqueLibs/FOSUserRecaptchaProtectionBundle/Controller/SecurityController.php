<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Services\ProtectionManager;

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
        /** @var ProtectionManager $protectionManager */
        $protectionManager = $this->container->get('uqe.fos_user_recaptcha_protection_bundle.protection_manager');

        $recaptchaSiteKey = null;
        if ($protectionManager->showRecaptchaOnLoginPage($this->container->get('request_stack')->getCurrentRequest())) {
            $recaptchaSiteKey = $protectionManager->getConfigurationManager()->getRecaptchaSiteKey();
        }

        return parent::renderLogin(array_merge($data, array(
            'uniquelibs_recaptcha_site_key' => $recaptchaSiteKey,
        )));
    }
}
