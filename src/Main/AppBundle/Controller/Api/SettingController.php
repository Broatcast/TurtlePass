<?php

namespace Main\AppBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Form\Type\RecaptchaSettingType;
use Main\AppBundle\Form\Type\SettingType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @REST\RouteResource("Setting")
 * @REST\NamePrefix("api_")
 */
class SettingController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Get the list of settings.",
     *   section = "Setting",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN"
     *   },
     *   filters={
     *      {"name"="query", "dataType"="string"}
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $queryBuilder = $this->get('main_app.services.setting_manager')->qbAllSettings();

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_app.query_builder_mapper.setting')),
            'api_get_settings',
            []
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Update settings.",
     *   section = "Setting",
     *   input = "Main\AppBundle\Form\Type\SettingType",
     *   statusCodes = {
     *     204 = "Returned when successfully updated",
     *     400 = "Returned when form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when password not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(SettingType::class);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $formSettings = $form->getData();

            $settingManager = $this->get('main_app.services.setting_manager');

            foreach ($formSettings as $key => $value) {
                if (in_array($key, Setting::getRecaptchaIds())) {
                    continue;
                }

                if ($value !== null) {
                    $setting = $settingManager->getSetting($key);

                    $setting->setValue((string)$value);

                    $settingManager->updateSetting($setting);
                }
            }

            return $this->view(null, 204);
        }

        return $this->view($form);
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Update settings.",
     *   section = "Setting",
     *   input = "Main\AppBundle\Form\Type\RecaptchaSettingType",
     *   statusCodes = {
     *     204 = "Returned when successfully updated",
     *     400 = "Returned when form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when password not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @REST\Post("/settings/recaptcha")
     *
     * @return View
     */
    public function postRecaptchaAction(Request $request)
    {
        $form = $this->createForm(RecaptchaSettingType::class);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $formSettings = $form->getData();

            $settingManager = $this->get('main_app.services.setting_manager');

            foreach ($formSettings as $key => $value) {
                if (!in_array($key, Setting::getRecaptchaIds())) {
                    continue;
                }

                if ($value !== null) {
                    $setting = $settingManager->getSetting($key);

                    $setting->setValue((string)$value);

                    $settingManager->updateSetting($setting);
                }
            }

            return $this->view(null, 204);
        }

        return $this->view($form);
    }
}
