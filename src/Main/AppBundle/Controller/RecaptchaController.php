<?php

namespace Main\AppBundle\Controller;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Form\Type\RecaptchaSettingType;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class RecaptchaController extends Controller
{
    /**
     * @Route("/recaptcha")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(Request $request)
    {
        $settingManager = $this->get('main_app.services.setting_manager');

        $settingPrivate = $settingManager->getSetting(Setting::ID_RECAPTCHA_PRIVATE_KEY);
        $settingSite = $settingManager->getSetting(Setting::ID_RECAPTCHA_SITE_KEY);

        $isActive = false;

        if ($settingPrivate->getValueAllowNull() !== null && $settingSite->getValueAllowNull() !== null) {
            $isActive = true;
        }

        $form = $this->createForm(RecaptchaSettingType::class, null, [
            'recaptcha_required' => true,
        ]);

        $form->handleRequest($request);

        $data = [];

        if ($form->isValid()) {
            $formSettings = $form->getData();

            if ($request->get('g-recaptcha-response') !== null) {
                $captcha = new ReCaptcha($formSettings[Setting::ID_RECAPTCHA_PRIVATE_KEY]);

                $success = $captcha->verify($request->get('g-recaptcha-response'),$request->getClientIp())->isSuccess();

                if ($success === true) {


                    $settingPrivate->setValue($formSettings[Setting::ID_RECAPTCHA_PRIVATE_KEY]);
                    $settingManager->updateSetting($settingPrivate);

                    $settingSite->setValue($formSettings[Setting::ID_RECAPTCHA_SITE_KEY]);
                    $settingManager->updateSetting($settingSite);

                    return $this->redirectToRoute('main_app_recaptcha_index');
                } else {
                    $form->get(Setting::ID_RECAPTCHA_PRIVATE_KEY)->addError(new FormError('Invalid private key or domain not allowed in Recaptcha configuration.'));
                }
            }

            $data['recaptcha'] = $formSettings[Setting::ID_RECAPTCHA_SITE_KEY];
        }

        return $this->render('AppBundle::recaptcha.html.twig', array_merge([
            'form' => $form->createView(),
            'error' => '',
            'is_active' => $isActive,
        ], $data));
    }

    /**
     * @Route("/recaptcha/disable")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function disableAction()
    {
        $settingManager = $this->get('main_app.services.setting_manager');

        $settingPrivate = $settingManager->getSetting(Setting::ID_RECAPTCHA_PRIVATE_KEY);
        $settingSite = $settingManager->getSetting(Setting::ID_RECAPTCHA_SITE_KEY);

        $settingPrivate->setValue("");
        $settingManager->updateSetting($settingPrivate);

        $settingSite->setValue("");
        $settingManager->updateSetting($settingSite);

        return $this->redirectToRoute('main_app_recaptcha_index');
    }
}
