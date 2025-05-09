<?php

namespace Main\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Google\Authenticator\GoogleAuthenticator;
use Main\AppBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @REST\RouteResource("Secret")
 * @REST\NamePrefix("api_")
 */
class SecretController extends FOSRestController
{
    /**
     * @ApiDoc(
     *   description = "Has user google authenticator secret.",
     *   section = "Secret",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @return JsonResponse
     */
    public function cgetAction()
    {
        return new JsonResponse([
            'has_secret' => $this->getUser()->hasSecret(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Create google authenticator secret.",
     *   section = "Secret",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     409 = "Returned when already has secret"
     *   }
     * )
     *
     * @return JsonResponse|View
     */
    public function postAction(Request $request)
    {
        if ($this->getUser()->hasSecret()) {
            return $this->view(null, Response::HTTP_CONFLICT);
        }

        $authenticator = new GoogleAuthenticator();

        $secret = $authenticator->generateSecret();

        return new JsonResponse([
            'secret' => $secret,
            'url' => $authenticator->getUrl($this->getUser()->getUsername(), $request->getHost(), $secret),
        ]);
    }

    /**
     * @param string $secret
     * @param string $code
     *
     * @ApiDoc(
     *   description = "Connect google authenticator secret.",
     *   section = "Secret",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when failed",
     *     401 = "Returned when invalid oauth token",
     *     409 = "Returned when already has secret"
     *   }
     * )
     *
     * @REST\Put("/secrets/{secret}/{code}")
     *
     * @return JsonResponse|View
     */
    public function putAction($secret, $code)
    {
        if ($this->getUser()->hasSecret()) {
            return $this->view(null, Response::HTTP_CONFLICT);
        }

        $authenticator = new GoogleAuthenticator();

        if ($authenticator->checkCode($secret, $code)) {
            $user = $this->getUser();

            $user->setSecret($secret);

            $this->get('main_user.services.user_manager')->updateUser($user);

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->view([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => 'Invalid secret or code.',
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @ApiDoc(
     *   description = "Delete google authenticator secret.",
     *   section = "Secret",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     404 = "Returned when user has no secret"
     *   }
     * )
     * @REST\Delete("/secrets")
     *
     * @return View
     */
    public function deleteAction()
    {
        if (!$this->getUser()->hasSecret()) {
            return $this->view(null, Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();

        $user->setSecret(null);

        $this->get('main_user.services.user_manager')->updateUser($user);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
