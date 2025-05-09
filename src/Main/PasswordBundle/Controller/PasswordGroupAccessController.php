<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Entity\PasswordGroupAccess;
use Main\PasswordBundle\Form\Type\AddPasswordGroupAccessType;
use Main\PasswordBundle\Form\Type\EditPasswordGroupAccessType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @REST\RouteResource("PasswordGroup")
 * @REST\NamePrefix("api_")
 */
class PasswordGroupAccessController extends FOSRestController
{
    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Get the list of password group accesses.",
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
    public function cgetAccessesAction(Request $request, PasswordGroup $passwordGroup)
    {
        $passwordAccessManager = $this->get('main_password.services.password_access_manager');

        $queryBuilder = $passwordAccessManager->qbAllPasswordGroupAccessesByPasswordGroup($passwordGroup);

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password_group_access')),
            'api_get_passwordgroups_accesses',
            ['passwordGroup' => $passwordGroup->getId()]
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Add password group access by id.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\AddPasswordAccessType",
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
     */
    public function postAccessAction(Request $request, PasswordGroup $passwordGroup)
    {
        $access = new PasswordGroupAccess();
        $access->setPasswordGroup($passwordGroup);

        $form = $this->createForm(AddPasswordGroupAccessType::class, $access);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordAccessManager = $this->get('main_password.services.password_access_manager');

            $passwordAccessManager->updatePasswordGroupAccess($access);

            return View::create($access, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request             $request
     * @param PasswordGroup       $passwordGroup
     * @param PasswordGroupAccess $passwordGroupAccess
     *
     * @ApiDoc(
     *   description = "Update password group access by id.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\EditPasswordAccessType",
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
     */
    public function putAccessAction(Request $request, PasswordGroup $passwordGroup, PasswordGroupAccess $passwordGroupAccess)
    {
        if ($passwordGroupAccess->getPasswordGroup()->getId() != $passwordGroup->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordGroupAccessAllowed($this->getUser(), $passwordGroupAccess)) {
            return $this->view(null, 409);
        }

        $form = $this->createForm(EditPasswordGroupAccessType::class, $passwordGroupAccess);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordAccessManager = $this->get('main_password.services.password_access_manager');

            $passwordAccessManager->updatePasswordGroupAccess($passwordGroupAccess);

            return View::create($passwordGroupAccess)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param PasswordGroup       $passwordGroup
     * @param PasswordGroupAccess $passwordGroupAccess
     *
     * @ApiDoc(
     *   description = "Delete password group access by id.",
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
     */
    public function deleteAccessAction(PasswordGroup $passwordGroup, PasswordGroupAccess $passwordGroupAccess)
    {
        if ($passwordGroupAccess->getPasswordGroup()->getId() != $passwordGroup->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordGroupAccessAllowed($this->getUser(), $passwordGroupAccess)) {
            return $this->view(null, 409);
        }

        $passwordAccessManager = $this->get('main_password.services.password_access_manager');

        $passwordAccessManager->removePasswordGroupAccess($passwordGroupAccess);

        return View::create(null, 204);
    }
}
