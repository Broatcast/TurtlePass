<?php

namespace Main\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use Main\AppBundle\Controller\FOSRestController;
use Main\UserBundle\Form\Model\ChangePasswordModel;
use Main\UserBundle\Form\Type\ChangePasswordType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @REST\RouteResource("Password")
 * @REST\NamePrefix("api_")
 */
class PasswordController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Change user password.",
     *   section = "User",
     *   input = "Main\UserBundle\Form\Type\ChangePasswordType",
     *   statusCodes = {
     *     204 = "Returned when successfully changed",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @REST\Post("/userpassword/change")
     * @Security("is_granted(constant('\\Main\\UserBundle\\Security\\UserVoter::CHANGE_PASSWORD'), user)")
     *
     * @return Response
     */
    public function postChangeAction(Request $request)
    {
        $changePasswordModel = new ChangePasswordModel();

        $form = $this->createForm(ChangePasswordType::class, $changePasswordModel);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $user->setPlainPassword($changePasswordModel->getNewPassword());

            $this->get('fos_user.user_manager')->updateUser($user);

            return $this->handleView($this->view(null, 204));
        }

        return $this->handleView($this->view($form));
    }
}
