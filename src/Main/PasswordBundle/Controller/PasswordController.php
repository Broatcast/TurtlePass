<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Container\LogKeys;
use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Form\Type\EditPasswordType;
use Main\PasswordBundle\Form\Type\MovePasswordType;
use Main\PasswordBundle\Security\PasswordGroupVoter;
use Main\PasswordBundle\Security\PasswordVoter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RouteResource("Password")
 * @NamePrefix("api_")
 */
class PasswordController extends FOSRestController
{
    /**
     * @param string $query
     *
     * @ApiDoc(
     *   description = "Search Password",
     *   section = "Password",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @return View
     */
    public function cgetSearchAction($query)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        return View::create($passwordManager->searchPasswordByUserAndQuery($this->getUser(), $query))
            ->setContext($this->getContextByUser($this->getUser(), ['ShowPasswordGroup']));
    }

    /**
     * @param int $password
     *
     * @ApiDoc(
     *   description = "Get password by id.",
     *   section = "Password",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function getAction($password)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $password = $passwordManager->getPasswordByIdAndUser($password, $this->getUser());

        if (!$password instanceof Password) {
            throw new NotFoundHttpException('Password not found.');
        }

        $this->denyAccessUnlessGranted(PasswordVoter::VIEW, $password);

        return View::create($password)
            ->setContext($this->getContextByUser($this->getUser(), $passwordManager->getPasswordSerializerGroups($password, $this->getUser())));
    }

    /**
     * @param int $password
     *
     * @ApiDoc(
     *   description = "Get password group by password id.",
     *   section = "Password",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Get("/passwords/{password}/passwordgroup")
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function getPasswordGroupAction($password)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $password = $passwordManager->getPasswordByIdAndUser($password, $this->getUser());

        if (!$password instanceof Password) {
            throw new NotFoundHttpException('Password not found.');
        }

        $this->denyAccessUnlessGranted(PasswordVoter::VIEW, $password);

        return View::create($password->getPasswordGroup())
            ->setContext($this->getContextByUser($this->getUser(), ['ShowParentPasswordGroups', 'ShowPasswordGroupDescription', 'ShowAccess']));
    }

    /**
     * @param int $password
     *
     * @ApiDoc(
     *   description = "Confirm password by id.",
     *   section = "Password",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found",
     *     409 = "Returned when password logging is not enabled."
     *   }
     * )
     *
     * @Rest\Post("/passwords/{password}/confirm")
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function postConfirmAction($password)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $password = $passwordManager->getPasswordByIdAndUser($password, $this->getUser());

        if (!$password instanceof Password) {
            throw new NotFoundHttpException('Password not found.');
        }

        if (!$password->isLogEnabled()) {
            return $this->view(null, Response::HTTP_CONFLICT);
        }

        $passwordLogManager = $this->get('main_password.services.password_log_manager');

        $passwordLogManager->createPasswordLog($password, LogKeys::KEY_VIEW, $this->getUser());

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Delete password by id.",
     *   section = "Password",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::DELETE'), password)")
     *
     * @return View
     */
    public function deleteAction(Password $password)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $passwordManager->deletePassword($password);

        return View::create(null, 204);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update password.",
     *   section = "Password",
     *   input = "Main\PasswordBundle\Form\Type\EditPasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putAction(Request $request, Password $password)
    {
        $form = $this->createForm(EditPasswordType::class, $password);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordManager = $this->get('main_password.services.password_manager');

            $password->setLastUpdateDate(new \DateTime());

            $passwordManager->updatePasswordByUser($password, $this->getUser());

            return View::create($password)
                ->setContext($this->getContextByUser($this->getUser(), $passwordManager->getPasswordSerializerGroups($password, $this->getUser())));
        }

        return $this->view($form);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Move password.",
     *   section = "Password",
     *   input = "Main\PasswordBundle\Form\Type\MovePasswordType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found",
     *     409 = "Returned when invalid parameters"
     *   }
     * )
     *
     * @REST\Put("/passwords/{password}/move", requirements={"password": "\d+"})
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::MOVE'), password)")
     *
     * @return View
     */
    public function putMoveAction(Request $request, Password $password)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $form = $this->createForm(MovePasswordType::class, $password);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $newPasswordGroup = $form->get('password_group')->getData();

            if ($password->getPasswordGroup()->getId() == $newPasswordGroup->getId()) {
                return $this->view(null, 409);
            }

            $this->denyAccessUnlessGranted(PasswordGroupVoter::ALLOW_AS_PARENT, $newPasswordGroup);

            $password->setPasswordGroup($newPasswordGroup);

            $passwordManager->movePasswordByUser($password, $this->getUser());

            return $this->view(null, 204);
        }

        return $this->view($form);
    }
}
