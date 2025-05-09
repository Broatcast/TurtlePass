<?php

namespace Main\LanguageBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\LanguageBundle\Form\Type\UserLanguageType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * @REST\RouteResource("Language")
 * @REST\NamePrefix("api_")
 */
class LanguageController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Get the list of Languages.",
     *   section = "Language",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $languageManager = $this->get('main_language.services.language_manager');
        $queryBuilder = $languageManager->qbAllLanguages();

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQuery(
            $request,
            $languageManager->translateQueryBuilder($filterManager->executeRequest($request, $queryBuilder, $this->get('main_language.query_builder_mapper.language'))),
            'api_get_languages',
            []
        );
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Change current language.",
     *   section = "Language",
     *   input = "Main\LanguageBundle\Form\Type\UserLanguageType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @return View
     */
    public function putAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserLanguageType::class, $user);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('main_user.services.user_manager')->updateUser($user);

            return $this->view(null, 204);
        }

        return $this->view($form);
    }
}
