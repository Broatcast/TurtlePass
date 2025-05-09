<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Container\LogKeys;
use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordShareLink;
use Main\PasswordBundle\Form\Type\PasswordShareLinkType;
use Main\PasswordBundle\Form\Type\PasswordType\BankAccount\BankAccountPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\CreditCard\CreditCardPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Email\EmailPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Plain\PlainPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Server\ServerPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\SoftwareLicense\SoftwareLicensePasswordType;
use Main\PasswordBundle\Model\PasswordShareLinkModel;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @REST\RouteResource("PasswordShareLink")
 * @REST\NamePrefix("api_")
 */
class ShareApiController extends FOSRestController
{
    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "List all password share links.",
     *   section = "Password",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when access denied",
     *     404 = "Returned when password not found"
     *   },
     *   filters={
     *      {"name"="limit", "dataType"="integer"},
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="query", "dataType"="string"},
     *      {"name"="sort", "dataType"="string"}
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::LIST_VIEW'), password)")
     * @Rest\Get("/passwords/{password}/shares")
     *
     * @return View
     */
    public function cgetAction(Request $request, Password $password)
    {
        $queryBuilder = $this->get('main_password.services.password_share_manager')->qbAllByPassword($password);

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password_share_link')),
            'api_get_passwordsharelinks',
            ['password' => $password->getId()]
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Get password by a share link id.",
     *   section = "Password Shares",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when not found"
     *   }
     * )
     *
     * @Rest\Get("/password-shares/{passwordShareLink}")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::VIEW'), passwordShareLink)")
     *
     * @return View
     *
     * @throws NotFoundHttpException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getAction(PasswordShareLink $passwordShareLink)
    {
        $password = $passwordShareLink->getPassword();

        if ($password->isLogEnabled()) {
            $passwordLogManager = $this->get('main_password.services.password_log_manager');
            $passwordLogManager->createPasswordLog($password, LogKeys::KEY_VIEW, $passwordShareLink, true);
        }

        $passwordShareManager = $this->get('main_password.services.password_share_manager');
        $passwordShareManager->increaseView($passwordShareLink);

        $context = $this->createContext();

        return View::create($passwordShareLink)->setContext($context);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Create a new password share link.",
     *   section = "Password",
     *   input="Main\PasswordBundle\Form\Type\PasswordShareLinkType",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @Rest\Post("/passwords/{password}/shares")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::CREATE'), password)")
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function postAction(Request $request, Password $password)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $password = $passwordManager->getPasswordByIdAndUser($password->getId(), $this->getUser());

        if (!$password instanceof Password) {
            throw new NotFoundHttpException('Password not found.');
        }

        $passwordShareLinkModel = new PasswordShareLinkModel();
        $form = $this->createForm(PasswordShareLinkType::class, $passwordShareLinkModel);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            $passwordShareManager = $this->get('main_password.services.password_share_manager');
            $passwordShareLink = $passwordShareManager->createShareLink($password, $passwordShareLinkModel, $passwordShareLinkModel->getMode());

            return $this->view($passwordShareLink, Response::HTTP_CREATED);
        }

        return $this->view($form);
    }


    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Update plain password.",
     *   section = "Password Shares",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\Plain\PlainPasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @REST\Put("/password-shares/{passwordShareLink}/plain")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::WRITE'), passwordShareLink)")
     *
     * @return View
     */
    public function putPasswordPlainAction(Request $request, PasswordShareLink $passwordShareLink)
    {
        return $this->editPasswordController($request, $passwordShareLink, PlainPasswordType::class);
    }

    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Update bank account password.",
     *   section = "Password Shares",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\BankAccount\BankAccountPasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @REST\Put("/password-shares/{passwordShareLink}/bank-account")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::WRITE'), passwordShareLink)")
     *
     * @return View
     */
    public function putPasswordBankAccountAction(Request $request, PasswordShareLink $passwordShareLink)
    {
        return $this->editPasswordController($request, $passwordShareLink, BankAccountPasswordType::class);
    }

    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Update credit card password.",
     *   section = "Password Shares",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\CreditCard\CreditCardPasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @REST\Put("/password-shares/{passwordShareLink}/credit-card")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::WRITE'), passwordShareLink)")
     *
     * @return View
     */
    public function putPasswordCreditCardAction(Request $request, PasswordShareLink $passwordShareLink)
    {
        return $this->editPasswordController($request, $passwordShareLink, CreditCardPasswordType::class);
    }

    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Update email.",
     *   section = "Password Shares",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\Email\EmailPasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @REST\Put("/password-shares/{passwordShareLink}/email")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::WRITE'), passwordShareLink)")
     *
     * @return View
     */
    public function putPasswordEmailAction(Request $request, PasswordShareLink $passwordShareLink)
    {
        return $this->editPasswordController($request, $passwordShareLink, EmailPasswordType::class);
    }

    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Update server.",
     *   section = "Password Shares",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\Server\ServerPasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @REST\Put("/password-shares/{passwordShareLink}/server")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::WRITE'), passwordShareLink)")
     *
     * @return View
     */
    public function putPasswordServerAction(Request $request, PasswordShareLink $passwordShareLink)
    {
        return $this->editPasswordController($request, $passwordShareLink, ServerPasswordType::class);
    }

    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Update software license.",
     *   section = "Password Shares",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\SoftwareLicense\SoftwareLicensePasswordType",
     *   statusCodes = {
     *     200 = "Returned when successfully updated",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @REST\Put("/password-shares/{passwordShareLink}/software-license")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::WRITE'), passwordShareLink)")
     *
     * @return View
     */
    public function putPasswordSoftwareLicenseAction(Request $request, PasswordShareLink $passwordShareLink)
    {
        return $this->editPasswordController($request, $passwordShareLink, SoftwareLicensePasswordType::class);
    }

    /**
     * @param Request           $request
     * @param PasswordShareLink $passwordShareLink
     * @param string            $formClass
     *
     * @return View
     */
    protected function editPasswordController(Request $request, PasswordShareLink $passwordShareLink, $formClass)
    {
        $password = $passwordShareLink->getPassword();

        $form = $this->createForm($formClass, $password, [
            'log_enabled_available' => false,
            'allow_add_custom_fields' => false,
            'allow_delete_custom_fields' => false,
        ]);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordManager = $this->get('main_password.services.password_manager');

            $password->setLastUpdateDate(new \DateTime());

            $passwordManager->updatePasswordByShareLink($password, $passwordShareLink);

            $context = $this->createContext();

            return View::create($password, Response::HTTP_OK)->setContext($context);
        }

        return $this->view($form);
    }

    /**
     * @param Password          $password
     * @param PasswordShareLink $passwordShareLink
     *
     * @ApiDoc(
     *   description = "Delete a password share link.",
     *   section = "Password",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password access denied",
     *     404 = "Returned when password not found"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordShareLinkVoter::REVOKE'), password)")
     * @Rest\Delete("/passwords/{password}/shares/{passwordShareLink}")
     *
     * @return View
     */
    public function deleteAction(Password $password, PasswordShareLink $passwordShareLink)
    {
        $passwordShareManager = $this->get('main_password.services.password_share_manager');

        $passwordShareManager->deleteShareLink($passwordShareLink);

        return View::create(null, 204);
    }

    /**
     * @return Context
     */
    private function createContext()
    {
        $context = new Context();
        $context->setGroups(['Default', 'ShowPassword', 'ShowAccess', 'ShowPasswordExtended', 'ShowNotice']);

        return $context;
    }
}
