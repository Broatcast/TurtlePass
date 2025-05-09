<?php

namespace Main\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\UserBundle\Entity\UserGroup;
use Main\UserBundle\Form\Type\UserGroupType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @REST\RouteResource("UserGroup")
 * @REST\NamePrefix("api_")
 */
class UserGroupController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Get the list of user groups.",
     *   section = "User Group",
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
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $queryBuilder = $this->get('main_user.services.user_group_manager')->qbAll();

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_user.query_builder_mapper.user_group')),
            'api_get_usergroups',
            []
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param UserGroup $userGroup
     *
     * @ApiDoc(
     *   description = "Get an user group by id.",
     *   section = "User Group",
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
    public function getAction(UserGroup $userGroup)
    {
        return $this->view($userGroup)->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Create an user group.",
     *   input = "Main\UserBundle\Form\Type\UserGroupType",
     *   section = "User Group",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postAction(Request $request)
    {
        $userGroup = new UserGroup();
        $form = $this->createForm(UserGroupType::class, $userGroup);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $userGroupManager = $this->get('main_user.services.user_group_manager');

            $userGroupManager->create($userGroup);

            return $this->view($userGroup, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request   $request
     * @param UserGroup $userGroup
     *
     * @ApiDoc(
     *   description = "Update an user group by id.",
     *   input = "Main\UserBundle\Form\Type\UserGroupType",
     *   section = "User Group",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user group not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function putAction(Request $request, UserGroup $userGroup)
    {
        $form = $this->createForm(UserGroupType::class, $userGroup);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $userGroupManager = $this->get('main_user.services.user_group_manager');

            $userGroupManager->update($userGroup);

            return $this->view($userGroup, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param UserGroup $userGroup
     *
     * @ApiDoc(
     *   description = "Delete an user group by id.",
     *   section = "User Group",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user group not found",
     *     409 = "Returned when allocated accesses or users"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAction(UserGroup $userGroup)
    {
        $passwordUserGroupAccessManager = $this->get('main_password.services.password_user_group_access_manager');

        if ($passwordUserGroupAccessManager->hasUserGroupAnyAccesses($userGroup)) {
            return $this->view(null, 409);
        }

        if (count($userGroup->getUsers()) > 0) {
            foreach ($userGroup->getUsers() as $user) {
                if (!$user->isDeleted()) {
                    return $this->view(null, 409);
                }
            }
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $userGroupManager = $this->get('main_user.services.user_group_manager');

            $userGroupManager->remove($userGroup);

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $this->view(null, 204);
    }
}
