<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\Password;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @REST\RouteResource("Password")
 * @REST\NamePrefix("api_")
 */
class PasswordLogController extends FOSRestController
{
    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Get the list of password logs.",
     *   section = "Password",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when access denied",
     *     404 = "Returned when password not found"
     *   },
     *   filters={
     *      {"name"="limit", "dataType"="integer"},
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="query", "dataType"="string"},
     *      {"name"="sort", "dataType"="string"}
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::LOGS'), password)")
     *
     * @return View
     */
    public function cgetLogsAction(Request $request, Password $password)
    {
        $queryBuilder = $this->get('main_password.services.password_log_manager')->qbAllPasswordLogsByPassword($password);

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password_log')),
            'api_get_passwords_logs',
            ['password' => $password->getId()]
        )->setContext($this->getContextByUser($this->getUser(), ['ShowUser']));
    }
}
