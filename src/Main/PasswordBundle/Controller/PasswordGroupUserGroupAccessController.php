<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupUserGroupAccess;
use Main\PasswordBundle\Form\Type\AddPasswordGroupUserGroupAccessType;
use Main\PasswordBundle\Form\Type\EditPasswordGroupUserGroupAccessType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @REST\RouteResource("PasswordGroup")
 * @REST\NamePrefix("api_")
 */
class PasswordGroupUserGroupAccessController extends FOSRestController
{
    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Get the list of password group user group accesses.",
     *   section = "Password Group",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   },
     *   filters={
     *      {"name"="query", "dataType"="string"}
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::MANAGE_ACCESS'), passwordGroup)")
     *
     * @return View
     */
    public function cgetUsergroupaccessesAction(Request $request, PasswordGroup $passwordGroup)
    {
        $passwordUserGroupAccessManager = $this->get('main_password.services.password_user_group_access_manager');

        $queryBuilder = $passwordUserGroupAccessManager->qbAllPasswordGroupUserGroupAccessesByPasswordGroup($passwordGroup);

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password_group_user_group_access')),
            'api_get_passwordgroups_usergroupaccesses',
            ['passwordGroup' => $passwordGroup->getId()]
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Add password group user group access by id.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\AddPasswordGroupUserGroupAccessType",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::MANAGE_ACCESS'), passwordGroup)")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUsergroupaccessesAction(Request $request, PasswordGroup $passwordGroup)
    {
        $access = new PasswordGroupUserGroupAccess();
        $access->setPasswordGroup($passwordGroup);

        $form = $this->createForm(AddPasswordGroupUserGroupAccessType::class, $access);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordAccessManager = $this->get('main_password.services.password_user_group_access_manager');

            $passwordAccessManager->updatePasswordGroupUserGroupAccess($access);

            return View::create($access, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request                      $request
     * @param PasswordGroup                $passwordGroup
     * @param PasswordGroupUserGroupAccess $passwordGroupUserGroupAccess
     *
     * @ApiDoc(
     *   description = "Update password group user group access by id.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\EditPasswordGroupUserGroupAccessType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password or access not found",
     *     409 = "Returned when trying to update your own access"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::MANAGE_ACCESS'), passwordGroup)")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function putUsergroupaccessesAction(Request $request, PasswordGroup $passwordGroup, PasswordGroupUserGroupAccess $passwordGroupUserGroupAccess)
    {
        if ($passwordGroupUserGroupAccess->getPasswordGroup()->getId() != $passwordGroup->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordGroupUserGroupAccessAllowed($this->getUser(), $passwordGroupUserGroupAccess)) {
            return $this->view(null, 409);
        }

        $form = $this->createForm(EditPasswordGroupUserGroupAccessType::class, $passwordGroupUserGroupAccess);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordAccessManager = $this->get('main_password.services.password_user_group_access_manager');

            $passwordAccessManager->updatePasswordGroupUserGroupAccess($passwordGroupUserGroupAccess);

            return View::create($passwordGroupUserGroupAccess)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param PasswordGroup                $passwordGroup
     * @param PasswordGroupUserGroupAccess $passwordGroupUserGroupAccess
     *
     * @ApiDoc(
     *   description = "Delete password group user group access by id.",
     *   section = "Password Group",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password or access not found",
     *     409 = "Returned when trying to delete your own access"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::MANAGE_ACCESS'), passwordGroup)")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteUsergroupaccessesAction(PasswordGroup $passwordGroup, PasswordGroupUserGroupAccess $passwordGroupUserGroupAccess)
    {
        if ($passwordGroupUserGroupAccess->getPasswordGroup()->getId() != $passwordGroup->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordGroupUserGroupAccessAllowed($this->getUser(), $passwordGroupUserGroupAccess)) {
            return $this->view(null, 409);
        }

        $passwordAccessManager = $this->get('main_password.services.password_user_group_access_manager');

        $passwordAccessManager->removePasswordGroupUserGroupAccess($passwordGroupUserGroupAccess);

        return View::create(null, 204);
    }
}
