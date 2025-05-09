<?php

namespace Main\FOSUserRecaptchaProtectionBundle\Controller;

use Main\AppBundle\Entity\Setting;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Controller\ResettingController as BaseResettingController;

/**
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 * @author Marvin Rind        <kontakt@marvinrind.de>
 */
class ResettingController extends BaseResettingController
{
    /**
     * Request reset user password: show form
     */
    public function requestAction()
    {
        $settingManager = $this->container->get('main_app.services.setting_manager');

        if ($settingManager->getSetting(Setting::ID_ENABLE_PASSWORD_RESET)->getValue() == '0') {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        return parent::requestAction();
    }

    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction(Request $request)
    {
        return parent::sendEmailAction($request);
    }
}
