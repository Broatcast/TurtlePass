<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordAccess;
use Main\PasswordBundle\Form\Type\AddPasswordAccessType;
use Main\PasswordBundle\Form\Type\EditPasswordAccessType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @RouteResource("Password")
 * @NamePrefix("api_")
 */
class PasswordAccessController extends FOSRestController
{
    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Get the list of password accesses.",
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
    public function cgetAccessesAction(Request $request, Password $password)
    {
        $passwordAccessManager = $this->get('main_password.services.password_access_manager');

        $queryBuilder = $passwordAccessManager->qbAllPasswordAccessesByPassword($password);

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password_access')),
            'api_get_passwords_accesses',
            ['password' => $password->getId()]
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Add password access by id.",
     *   section = "Password",
     *   input = "Main\PasswordBundle\Form\Type\AddPasswordAccessType",
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
     */
    public function postAccessAction(Request $request, Password $password)
    {
        $access = new PasswordAccess();
        $access->setPassword($password);

        $form = $this->createForm(AddPasswordAccessType::class, $access);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordAccessManager = $this->get('main_password.services.password_access_manager');

            $passwordAccessManager->updatePasswordAccess($access);

            return View::create($access, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request        $request
     * @param Password       $password
     * @param PasswordAccess $passwordAccess
     *
     * @ApiDoc(
     *   description = "Update password access by id.",
     *   section = "Password",
     *   input = "Main\PasswordBundle\Form\Type\EditPasswordAccessType",
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
     */
    public function putAccessAction(Request $request, Password $password, PasswordAccess $passwordAccess)
    {
        if ($passwordAccess->getPassword()->getId() != $password->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordAccessAllowed($this->getUser(), $passwordAccess)) {
            return $this->view(null, 409);
        }

        $form = $this->createForm(EditPasswordAccessType::class, $passwordAccess);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordAccessManager = $this->get('main_password.services.password_access_manager');

            $passwordAccessManager->updatePasswordAccess($passwordAccess);

            return View::create($passwordAccess, 200)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Password       $password
     * @param PasswordAccess $passwordAccess
     *
     * @ApiDoc(
     *   description = "Delete password access by id.",
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
     */
    public function deleteAccessAction(Password $password, PasswordAccess $passwordAccess)
    {
        if ($passwordAccess->getPassword()->getId() != $password->getId()) {
            throw new AccessDeniedException('Access denied');
        }

        $accessManager = $this->get('main_password.services.access_manager');

        if (!$accessManager->isEditingPasswordAccessAllowed($this->getUser(), $passwordAccess)) {
            return $this->view(null, 409);
        }

        $passwordAccessManager = $this->get('main_password.services.password_access_manager');

        $passwordAccessManager->removePasswordAccess($passwordAccess);

        return View::create(null, 204);
    }
}
