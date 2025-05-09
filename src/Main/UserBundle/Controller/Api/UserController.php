<?php

namespace Main\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\UserBundle\Entity\User;
use Main\UserBundle\Form\Type\AddUserType;
use Main\UserBundle\Form\Type\EditOwnUserType;
use Main\UserBundle\Form\Type\EditUserType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @REST\RouteResource("User")
 * @REST\NamePrefix("api_")
 */
class UserController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Get the list of users.",
     *   section = "User",
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
        $queryBuilder = $this->get('main_user.services.user_manager')->qbAllNotDeletedUsers();

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_user.query_builder_mapper.user')),
            'api_get_users',
            []
        )->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param User $user
     *
     * @ApiDoc(
     *   description = "Get an user by id.",
     *   section = "User",
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
    public function getAction(User $user)
    {
        return $this->view($user)->setContext($this->getContextByUser($this->getUser()));
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Create an user.",
     *   input = "Main\UserBundle\Form\Type\AddUserType",
     *   section = "User",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $user = new User();
        $user->setEnabled(true);

        $form = $this->createForm(AddUserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('main_user.services.user_manager');

            $user->setPlainPassword($form->get('password')->getData());
            $user->setLanguage($this->getUser()->getLanguage());

            if ($form->get('admin')->getData()) {
                $user->addRole('ROLE_ADMIN');
            }

            $userManager->createGivenUser($user);

            return $this->view($user, 201)->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request $request
     * @param User    $user
     *
     * @ApiDoc(
     *   description = "Update an user by id.",
     *   input = "Main\UserBundle\Form\Type\EditUserType",
     *   section = "User",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user not found"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     */
    public function putAction(Request $request, User $user)
    {
        $form = $this->createForm(EditUserType::class, $user, [
            'own_user' => $user->getId() == $this->getUser()->getId(),
        ]);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('main_user.services.user_manager');

            if ($user->getId() !== $this->getUser()->getId()) {
                if ($form->get('password')->getData()) {
                    $user->setPlainPassword($form->get('password')->getData());
                }

                if ($form->get('admin')->getData()) {
                    if (!$user->hasRole('ROLE_ADMIN')) {
                        $user->addRole('ROLE_ADMIN');
                    }
                } else {
                    $user->removeRole('ROLE_ADMIN');
                    $user->removeRole('ROLE_SUPER_ADMIN');
                }
            }

            $userManager->updateUser($user);

            return $this->view($user, 200)->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Update your own details.",
     *   input = "Main\UserBundle\Form\Type\EditOwnUserType",
     *   section = "User",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_USER",
     *     404 = "Returned when user not found"
     *   }
     * )
     * @REST\Put("/user")
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return View
     */
    public function putOwnAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditOwnUserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('main_user.services.user_manager');

            $userManager->updateUser($user);

            return $this->view($user, 200)->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param User $user
     *
     * @ApiDoc(
     *   description = "Delete an user by id.",
     *   section = "User",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user not found",
     *     409 = "Returned when trying to delete yourself"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAction(User $user)
    {
        if ($user->getId() == $this->getUser()->getId()) {
            return $this->view(null, 409);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $userManager = $this->get('main_user.services.user_delete_manager');

            $userManager->deleteUser($user);

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $this->view(null, 204);
    }

    /**
     * @param User $user
     *
     * @ApiDoc(
     *   description = "Deactivate an user by id.",
     *   section = "User",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user not found",
     *     409 = "Returned when trying to deactivate yourself"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @REST\Post("/users/{user}/deactivate")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postDeactivateAction(User $user)
    {
        if ($user->getId() == $this->getUser()->getId()) {
            return $this->view(null, 409);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $userManager = $this->get('main_user.services.user_delete_manager');

            $userManager->deactivateUser($user);

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $this->view(null, 204);
    }

    /**
     * @param User $user
     *
     * @ApiDoc(
     *   description = "Activate an user by id.",
     *   section = "User",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user not found",
     *     409 = "Returned when trying to activate yourself"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @REST\Post("/users/{user}/activate")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postActivateAction(User $user)
    {
        if ($user->getId() == $this->getUser()->getId()) {
            return $this->view(null, 409);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->getConnection()->beginTransaction();

        try {
            $userManager = $this->get('main_user.services.user_delete_manager');

            $userManager->activateUser($user);

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $this->view(null, 204);
    }

    /**
     * @param User $user
     *
     * @ApiDoc(
     *   description = "Delete google authenticator by user id.",
     *   section = "User",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when missing role: ROLE_ADMIN",
     *     404 = "Returned when user not found",
     *     409 = "Returned when trying to delete at yourself"
     *   }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteSecretAction(User $user)
    {
        if ($user->getId() == $this->getUser()->getId()) {
            return $this->view(null, 409);
        }

        if (!$user->hasSecret()) {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        $userManager = $this->get('main_user.services.user_manager');

        $user->setSecret(null);

        $userManager->updateUser($user);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
