<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\PasswordType\BankAccountPassword;
use Main\PasswordBundle\Entity\PasswordType\CreditCardPassword;
use Main\PasswordBundle\Entity\PasswordType\EmailPassword;
use Main\PasswordBundle\Entity\PasswordType\ServerPassword;
use Main\PasswordBundle\Entity\PasswordType\SoftwareLicensePassword;
use Main\PasswordBundle\Form\Type\PasswordType\BankAccount\BankAccountPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\CreditCard\CreditCardPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Email\EmailPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Plain\PlainPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\Server\ServerPasswordType;
use Main\PasswordBundle\Form\Type\PasswordType\SoftwareLicense\SoftwareLicensePasswordType;
use Symfony\Component\HttpFoundation\Response;
use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordGroup;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @REST\RouteResource("PasswordGroup")
 * @REST\NamePrefix("api_")
 */
class AddPasswordController extends FOSRestController
{
    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create plain password.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\Plain\PlainPasswordType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Post("/passwordgroups/{passwordGroup}/passwords/plain")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::ADD_PASSWORD'), passwordGroup)")
     *
     * @return View
     */
    public function postPasswordPlainAction(Request $request, PasswordGroup $passwordGroup)
    {
        return $this->addPasswordController($request, $passwordGroup, new Password(), PlainPasswordType::class);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create bank account.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\BankAccount\BankAccountPasswordType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Post("/passwordgroups/{passwordGroup}/passwords/bank-account")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::ADD_PASSWORD'), passwordGroup)")
     *
     * @return View
     */
    public function postPasswordBankaccountAction(Request $request, PasswordGroup $passwordGroup)
    {
        return $this->addPasswordController($request, $passwordGroup, new BankAccountPassword(), BankAccountPasswordType::class);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create credit card.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\CreditCard\CreditCardPasswordType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Post("/passwordgroups/{passwordGroup}/passwords/credit-card")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::ADD_PASSWORD'), passwordGroup)")
     *
     * @return View
     */
    public function postPasswordCreditcardAction(Request $request, PasswordGroup $passwordGroup)
    {
        return $this->addPasswordController($request, $passwordGroup, new CreditCardPassword(), CreditCardPasswordType::class);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create email.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\Email\EmailPasswordType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Post("/passwordgroups/{passwordGroup}/passwords/email")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::ADD_PASSWORD'), passwordGroup)")
     *
     * @return View
     */
    public function postPasswordEmailAction(Request $request, PasswordGroup $passwordGroup)
    {
        return $this->addPasswordController($request, $passwordGroup, new EmailPassword(), EmailPasswordType::class);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create server.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\Server\ServerPasswordType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Post("/passwordgroups/{passwordGroup}/passwords/server")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::ADD_PASSWORD'), passwordGroup)")
     *
     * @return View
     */
    public function postPasswordServerAction(Request $request, PasswordGroup $passwordGroup)
    {
        return $this->addPasswordController($request, $passwordGroup, new ServerPassword(), ServerPasswordType::class);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create software license.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\PasswordType\SoftwareLicense\SoftwareLicensePasswordType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Post("/passwordgroups/{passwordGroup}/passwords/software-license")
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::ADD_PASSWORD'), passwordGroup)")
     *
     * @return View
     */
    public function postPasswordSoftwarelicenseAction(Request $request, PasswordGroup $passwordGroup)
    {
        return $this->addPasswordController($request, $passwordGroup, new SoftwareLicensePassword(), SoftwareLicensePasswordType::class);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     * @param Password      $newPassword
     * @param string        $formClass
     *
     * @return View
     */
    protected function addPasswordController(Request $request, PasswordGroup $passwordGroup, Password $newPassword, $formClass)
    {
        $newPassword->setPasswordGroup($passwordGroup);

        $form = $this->createForm($formClass, $newPassword);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordManager = $this->get('main_password.services.password_manager');

            $passwordManager->createPasswordByUser($newPassword, $this->getUser());

            return View::create($newPassword, Response::HTTP_CREATED)
                ->setContext($this->getContextByUser($this->getUser(), ['ShowNotice']));
        }

        return $this->view($form);
    }
}
