<?php

namespace Main\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\UserBundle\Entity\LoginAccess;
use Main\UserBundle\Form\Type\LoginAccessType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @REST\RouteResource("LoginAccess")
 * @REST\NamePrefix("api_")
 */
class LoginAccessController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Get the list of users.",
     *   section = "Login Access",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN"
     *   },
     *   filters={
     *      {"name"="query", "dataType"="string"}
     *   }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $queryBuilder = $this->get('main_user.entity.repository.login_access_repository')->qbAll();

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_user.query_builder_mapper.login_access')),
            'api_get_loginaccesses',
            []
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param LoginAccess $loginAccess
     *
     * @ApiDoc(
     *   description = "Get an login access by id.",
     *   section = "Login Access",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user was not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function getAction(LoginAccess $loginAccess)
    {
        return $this->view($loginAccess)->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Create a login access.",
     *   input = "Main\UserBundle\Form\Type\LoginAccessType",
     *   section = "Login Access",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $entity = new LoginAccess();

        $form = $this->createForm(LoginAccessType::class, $entity);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->setFromIp($form->get('from_ip')->getData());
            $entity->setToIp($form->get('to_ip')->getData());

            $repository = $this->get('main_user.entity.repository.login_access_repository');

            $repository->persist($entity);

            return $this->view($entity, Response::HTTP_CREATED)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request     $request
     * @param LoginAccess $loginAccess
     *
     * @ApiDoc(
     *   description = "Update a login access by id.",
     *   input = "Main\UserBundle\Form\Type\LoginAccessType",
     *   section = "Login Access",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when login access not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function putAction(Request $request, LoginAccess $loginAccess)
    {
        $form = $this->createForm(LoginAccessType::class, $loginAccess);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $loginAccess->setFromIp($form->get('from_ip')->getData());
            $loginAccess->setToIp($form->get('to_ip')->getData());

            $repository = $this->get('main_user.entity.repository.login_access_repository');

            $repository->persist($loginAccess);

            return $this->view($loginAccess, Response::HTTP_OK)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param LoginAccess $loginAccess
     *
     * @ApiDoc(
     *   description = "Delete a login access by id.",
     *   section = "Login Access",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when login access not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAction(LoginAccess $loginAccess)
    {
        $repository = $this->get('main_user.entity.repository.login_access_repository');

        $repository->remove($loginAccess);

        return $this->view(null, 204);
    }
}
