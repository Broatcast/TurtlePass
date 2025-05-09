<?php

namespace Main\AppBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use Main\AppBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @REST\RouteResource("Version")
 * @REST\NamePrefix("api_")
 */
class VersionController extends FOSRestController
{
    /**
     * @ApiDoc(
     *   description = "Get the current version.",
     *   section = "Version",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return JsonResponse
     */
    public function cgetAction()
    {
        $currentVersion = @file_get_contents('http://turtlepass.net/version.txt');

        return new JsonResponse([
            'installed_version' => $this->getParameter('app_version'),
            'current_version' => $currentVersion,
        ]);
    }
}
