<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Controller;

use ReCaptcha\ReCaptcha;
use FOS\UserBundle\Controller\ResettingController as BaseResettingController;
use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Services\ProtectionManager;

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
        /** @var ProtectionManager $protectionManager */
        $protectionManager = $this->container->get('uqe.fos_user_recaptcha_protection_bundle.protection_manager');

        $recaptchaSiteKey = null;
        if ($protectionManager->showRecaptchaOnResetPasswordPage($this->container->get('request_stack')->getCurrentRequest())) {
            $recaptchaSiteKey = $protectionManager->getConfigurationManager()->getRecaptchaSiteKey();
        }

        return $this->render('@FOSUser/Resetting/request.html.twig', [
            'uniquelibs_recaptcha_site_key' => $recaptchaSiteKey,
        ]);
    }

    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction(Request $request)
    {
        /** @var ProtectionManager $protectionManager */
        $protectionManager = $this->container->get('uqe.fos_user_recaptcha_protection_bundle.protection_manager');

        $recaptchaSiteKey = null;
        if ($protectionManager->showRecaptchaOnResetPasswordPage($this->container->get('request_stack')->getCurrentRequest())) {
            $recaptchaSiteKey = $protectionManager->getConfigurationManager()->getRecaptchaSiteKey();

            $captcha = new ReCaptcha($protectionManager->getConfigurationManager()->getRecaptchaPrivateKey());

            $success = $captcha->verify($this->container->get('request_stack')->getCurrentRequest()->get('g-recaptcha-response'), $this->container->get('request_stack')->getCurrentRequest()->getClientIp())->isSuccess();

            if ($success !== true) {
                return $this->render('@FOSUser/Resetting/request.html.twig', [
                    'invalid_recaptcha' => true,
                    'uniquelibs_recaptcha_site_key' => $recaptchaSiteKey,
                ]);
            }
        }

        return parent::sendEmailAction($request);
    }
}
