<?php

namespace Main\ApiBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\ApiBundle\Entity\AccessToken;
use Main\ApiBundle\Form\Type\Api\TokenType;
use Main\AppBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @REST\RouteResource("Token")
 * @REST\NamePrefix("api_")
 */
class TokenController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Get the list of your tokens.",
     *   section = "Token",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @return array
     */
    public function cgetAction(Request $request)
    {
        $tokenManager = $this->get('main_api.services.token_manager');

        $queryBuilder = $tokenManager->qbAllByUser($this->getUser());

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_api.query_builder_mapper.access_token')),
            'api_get_tokens',
            []
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param AccessToken $accessToken
     *
     * @ApiDoc(
     *   description = "Get Token",
     *   section = "Token",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when access denied",
     *     404 = "Returned when token not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\ApiBundle\\Security\\AccessTokenVoter::VIEW'), accessToken)")
     *
     * @return View
     */
    public function getAction(AccessToken $accessToken)
    {
        return $this->view($accessToken, 200)->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Create Token",
     *   input = "Main\ApiBundle\Form\Type\Api\TokenType",
     *   section = "Token",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     401 = "Returned when invalid oauth token",
     *     404 = "Returned when form has errors"
     *   }
     * )
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function postAction(Request $request)
    {
        $tokenManager = $this->get('main_api.services.token_manager');

        $accessToken = new AccessToken();
        $accessToken->setUser($this->getUser());
        $accessToken->setClient($tokenManager->getDefaultClient());
        $accessToken->setDefault(false);
        $accessToken->setCustom(true);
        $accessToken->setToken($tokenManager->generateToken());

        $form = $this->createForm(TokenType::class, $accessToken);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $tokenManager->createAccessToken($accessToken);

            return $this->view($accessToken, 201)->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request     $request
     * @param AccessToken $accessToken
     *
     * @ApiDoc(
     *   description = "Update Token",
     *   input = "Main\ApiBundle\Form\Type\Api\TokenType",
     *   section = "Token",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when access denied",
     *     404 = "Returned when token not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\ApiBundle\\Security\\AccessTokenVoter::EDIT'), accessToken)")
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function putAction(Request $request, AccessToken $accessToken)
    {
        $tokenManager = $this->get('main_api.services.token_manager');

        $form = $this->createForm(TokenType::class, $accessToken);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $tokenManager->updateAccessToken($accessToken);

            return $this->view($accessToken, 200)->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param AccessToken $accessToken
     *
     * @ApiDoc(
     *   description = "Delete Token",
     *   section = "Token",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when access denied",
     *     404 = "Returned when token not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\ApiBundle\\Security\\AccessTokenVoter::DELETE'), accessToken)")
     *
     * @return View
     */
    public function deleteAction(AccessToken $accessToken)
    {
        $tokenManager = $this->get('main_api.services.token_manager');

        $tokenManager->removeAccessToken($accessToken);

        return $this->view(null, 204);
    }
}
