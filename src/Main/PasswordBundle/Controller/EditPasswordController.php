<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Form\Type\PasswordType\BankAccount\BankAccountPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\CreditCard\CreditCardPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Email\EmailPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Plain\PlainPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Server\ServerPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\SoftwareLicense\SoftwareLicensePasswordType;
use Symfony\Component\HttpFoundation\Response;
use Main\PasswordBundle\Entity\Password;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @REST\RouteResource("PasswordGroup")
 * @REST\NamePrefix("api_")
 */
class EditPasswordController extends FOSRestController
{
    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update plain password.",
     *   section = "Password",
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
     * @REST\Put("/passwords/{password}/plain")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putPasswordPlainAction(Request $request, Password $password)
    {
        return $this->editPasswordController($request, $password, PlainPasswordType::class);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update bank account password.",
     *   section = "Password",
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
     * @REST\Put("/passwords/{password}/bank-account")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putPasswordBankAccountAction(Request $request, Password $password)
    {
        return $this->editPasswordController($request, $password, BankAccountPasswordType::class);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update credit card password.",
     *   section = "Password",
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
     * @REST\Put("/passwords/{password}/credit-card")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putPasswordCreditCardAction(Request $request, Password $password)
    {
        return $this->editPasswordController($request, $password, CreditCardPasswordType::class);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update email.",
     *   section = "Password",
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
     * @REST\Put("/passwords/{password}/email")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putPasswordEmailAction(Request $request, Password $password)
    {
        return $this->editPasswordController($request, $password, EmailPasswordType::class);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update server.",
     *   section = "Password",
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
     * @REST\Put("/passwords/{password}/server")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putPasswordServerAction(Request $request, Password $password)
    {
        return $this->editPasswordController($request, $password, ServerPasswordType::class);
    }

    /**
     * @param Request  $request
     * @param Password $password
     *
     * @ApiDoc(
     *   description = "Update software license.",
     *   section = "Password",
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
     * @REST\Put("/passwords/{password}/software-license")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordVoter::EDIT'), password)")
     *
     * @return View
     */
    public function putPasswordSoftwareLicenseAction(Request $request, Password $password)
    {
        return $this->editPasswordController($request, $password, SoftwareLicensePasswordType::class);
    }

    /**
     * @param Request  $request
     * @param Password $password
     * @param string   $formClass
     *
     * @return View
     */
    protected function editPasswordController(Request $request, Password $password, $formClass)
    {
        $form = $this->createForm($formClass, $password);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordManager = $this->get('main_password.services.password_manager');

            $password->setLastUpdateDate(new \DateTime());

            $passwordManager->updatePasswordByUser($password, $this->getUser());

            return View::create($password, Response::HTTP_OK)
                ->setContext($this->getContextByUser($this->getUser(), ['ShowNotice']));
        }

        return $this->view($form);
    }
}
