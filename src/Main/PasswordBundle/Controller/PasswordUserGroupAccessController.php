<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordUserGroupAccess;
use Main\PasswordBundle\Form\Type\AddPasswordUserGroupAccessType;
use Main\PasswordBundle\Form\Type\EditPasswordUserGroupAccessType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @RouteResource("Password")
 * @NamePrefix("api_")
 */
class PasswordUserGroupAccessController extends FOSRestController
{
    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Get the list of password user group accesses.",
     *   section = "Password",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when access denied",
     *     404 = "Returned when password not found"
     *   },
     *   filters={
     *      {"name"="query", "dataType"="string"}
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::VIEW_ACCESS'), password)")
     *
     * @return View
     */
    public function cgetUsergroupaccessesAction(Request $request, Password $password)
    {
        $passwordUserGroupAccessManager = $this->get('main_password.services.password_user_group_access_manager');

        $queryBuilder = $passwordUserGroupAccessManager->qbAllPasswordUserGroupAccessesByPassword($password);

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password_user_group_access')),
            'api_get_passwords_usergroupaccesses',
            ['password' => $password->getId()]
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Add password user group access by id.",
     *   section = "Password",
     *   input = "Main\PasswordBundle\Form\Type\AddPasswordUserGroupAccessType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::ADD_ACCESS'), password)")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUsergroupaccessesAction(Request $request, Password $password)
    {
        $access = new PasswordUserGroupAccess();
        $access->setPassword($password);

        $form = $this->createForm(AddPasswordUserGroupAccessType::class, $access);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordUserGroupAccessManager = $this->get('main_password.services.password_user_group_access_manager');

            $passwordUserGroupAccessManager->updatePasswordUserGroupAccess($access);

            return View::create($access, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request                 $request
     * @param Password                $password
     * @param PasswordUserGroupAccess $passwordUserGroupAccess
     *
     * @ApiDoc(
     *   description = "Update password user group access by id.",
     *   section = "Password",
     *   input = "Main\PasswordBundle\Form\Type\EditPasswordUserGroupAccessType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password or access not found",
     *     409 = "Returned when trying to update your own access without group access"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::UPDATE_ACCESS'), password)")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function putUsergroupaccessesAction(Request $request, Password $password, PasswordUserGroupAccess $passwordUserGroupAccess)
    {
        if ($passwordUserGroupAccess->getPassword()->getId() != $password->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordUserGroupAccessAllowed($this->getUser(), $passwordUserGroupAccess)) {
            return $this->view(null, 409);
        }

        $form = $this->createForm(EditPasswordUserGroupAccessType::class, $passwordUserGroupAccess);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordUserGroupAccessManager = $this->get('main_password.services.password_user_group_access_manager');

            $passwordUserGroupAccessManager->updatePasswordUserGroupAccess($passwordUserGroupAccess);

            return View::create($passwordUserGroupAccess, 200)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Password                $password
     * @param PasswordUserGroupAccess $passwordUserGroupAccess
     *
     * @ApiDoc(
     *   description = "Delete password user group access by id.",
     *   section = "Password",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password or access not found",
     *     409 = "Returned when trying to delete your own access without group access"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::DELETE_ACCESS'), password)")
     *
     * @return View
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteUsergroupaccessesAction(Password $password, PasswordUserGroupAccess $passwordUserGroupAccess)
    {
        if ($passwordUserGroupAccess->getPassword()->getId() != $password->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordUserGroupAccessAllowed($this->getUser(), $passwordUserGroupAccess)) {
            return $this->view(null, 409);
        }

        $passwordUserGroupAccessManager = $this->get('main_password.services.password_user_group_access_manager');

        $passwordUserGroupAccessManager->removePasswordUserGroupAccess($passwordUserGroupAccess);

        return View::create(null, 204);
    }
}
