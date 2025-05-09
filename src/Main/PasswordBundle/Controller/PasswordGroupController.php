<?php

namespace Main\PasswordBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Main\AppBundle\Controller\FOSRestController;
use Main\PasswordBundle\Entity\PasswordGroup;
use Main\PasswordBundle\Form\Type\AddPasswordGroupType;
use Main\PasswordBundle\Form\Type\MovePasswordGroupType;
use Main\PasswordBundle\Form\Type\SortPasswordGroupCollectionType;
use Main\PasswordBundle\Form\Type\SortPasswordGroupType;
use Main\PasswordBundle\Security\PasswordGroupVoter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @REST\RouteResource("PasswordGroup")
 * @REST\NamePrefix("api_")
 */
class PasswordGroupController extends FOSRestController
{
    /**
     * @ApiDoc(
     *   description = "Get the list of password groups.",
     *   section = "Password Group",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token"
     *   }
     * )
     *
     * @return View
     */
    public function cgetAction()
    {
        $passwordGroupManager = $this->get('main_password.services.password_group_manager');

        return View::create($passwordGroupManager->findAllPasswordGroupsByUser($this->getUser()));
    }

    /**
     * @param int $passwordGroup
     *
     * @ApiDoc(
     *   description = "Get password group by id.",
     *   section = "Password Group",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function getAction($passwordGroup)
    {
        $passwordGroupManager = $this->get('main_password.services.password_group_manager');

        $passwordGroup = $passwordGroupManager->getPasswordGroupByIdAndUser($passwordGroup, $this->getUser());

        if (!$passwordGroup instanceof PasswordGroup) {
            throw new NotFoundHttpException('Password group not found.');
        }

        $this->denyAccessUnlessGranted(PasswordGroupVoter::VIEW, $passwordGroup);

        return View::create($passwordGroup)
            ->setContext($this->getContextByUser($this->getUser(), ['ShowParentPasswordGroups', 'ShowPasswordGroupDescription', 'ShowAccess']));
    }

    /**
     * @param Request            $request
     * @param PasswordGroup|null $passwordGroup
     *
     * @ApiDoc(
     *   description = "Create password group.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\AddPasswordGroupType",
     *   statusCodes = {
     *     201 = "Returned when successfully created",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @return View
     */
    public function postAction(Request $request, PasswordGroup $passwordGroup = null)
    {
        if ($passwordGroup instanceof PasswordGroup) {
            $this->denyAccessUnlessGranted(PasswordGroupVoter::ALLOW_AS_PARENT, $passwordGroup);
        }

        $newPasswordGroup = new PasswordGroup();
        $newPasswordGroup->setParent($passwordGroup);

        $form = $this->createForm(AddPasswordGroupType::class, $newPasswordGroup);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordGroupManager = $this->get('main_password.services.password_group_manager');

            $passwordGroupManager->createPasswordGroupByUser($newPasswordGroup, $this->getUser());

            return View::create($newPasswordGroup, 201)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Update password group.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\AddPasswordGroupType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found"
     *   }
     * )
     *
     * @REST\Put("/passwordgroups/{passwordGroup}", requirements={"passwordGroup": "\d+"})
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::EDIT'), passwordGroup)")
     *
     * @return View
     */
    public function putAction(Request $request, PasswordGroup $passwordGroup)
    {
        $form = $this->createForm(AddPasswordGroupType::class, $passwordGroup);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordGroupManager = $this->get('main_password.services.password_group_manager');

            $passwordGroupManager->createPasswordGroup($passwordGroup);

            return View::create($passwordGroup)
                ->setContext($this->getContextByUser($this->getUser()));
        }

        return $this->view($form);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Move password group.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\MovePasswordGroupType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found",
     *     409 = "Returned when invalid parameters"
     *   }
     * )
     *
     * @REST\Put("/passwordgroups/{passwordGroup}/move", requirements={"passwordGroup": "\d+"})
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::MOVE'), passwordGroup)")
     *
     * @return View
     */
    public function putMoveAction(Request $request, PasswordGroup $passwordGroup)
    {
        $passwordGroupManager = $this->get('main_password.services.password_group_manager');

        $form = $this->createForm(MovePasswordGroupType::class, $passwordGroup);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $parentPasswordGroup = $form->get('parent')->getData();

            if ($parentPasswordGroup instanceof PasswordGroup) {
                if ($passwordGroup->getId() == $parentPasswordGroup->getId()) {
                    return $this->view(null, 409);
                }

                $this->denyAccessUnlessGranted(PasswordGroupVoter::ALLOW_AS_PARENT, $parentPasswordGroup);

                if ($passwordGroup->getParent() instanceof PasswordGroup) {
                    if ($passwordGroup->getParent()->getId() != $parentPasswordGroup->getId()) {
                        if ($passwordGroupManager->isPasswordGroupParentOfPasswordGroup($parentPasswordGroup, $passwordGroup)) {
                            return $this->view(null, 409);
                        }
                    }
                }
            }

            $passwordGroup->setParent($parentPasswordGroup);

            $passwordGroupManager->updatePasswordGroup($passwordGroup);

            return $this->view(null, 204);
        }

        return $this->view($form);
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *   description = "Change the sorting of the password groups.",
     *   section = "Password Group",
     *   input = "Main\PasswordBundle\Form\Type\SortPasswordGroupCollectionType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found",
     *     409 = "Returned when invalid parameters"
     *   }
     * )
     *
     * @REST\Put("/passwordgroups/sorting")
     *
     *  Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::SORT'))")
     *
     * @return View
     */
    public function putSortingAction(Request $request)
    {
        $form = $this->createForm(SortPasswordGroupCollectionType::class);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordGroupSortingManager = $this->get('main_password.services.password_group_sorting_manager');
            $passwordGroupSortingManager->saveUserSorting($this->getUser(), $form->get('sorting')->getData());

            return $this->view(null, 204);
        }

        return $this->view($form);
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Delete password group by id.",
     *   section = "Password Group",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     401 = "Returned when invalid oauth token",
     *     403 = "Returned when password group access denied",
     *     404 = "Returned when password group not found",
     *     409 = "Returned when trying to delete group with children"
     *   }
     * )
     *
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::DELETE'), passwordGroup)")
     *
     * @return View
     */
    public function deleteAction(PasswordGroup $passwordGroup)
    {
        $passwordGroupManager = $this->get('main_password.services.password_group_manager');

        if ($passwordGroupManager->countPasswordGroupByParent($passwordGroup)) {
            return $this->view(null, 409);
        }

        $passwordGroupManager->deletePasswordGroup($passwordGroup);

        return View::create(null, 204);
    }

    /**
     * @param Request       $request
     * @param PasswordGroup $passwordGroup
     *
     * @ApiDoc(
     *   description = "Get the list of passwords by password groups.",
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
     * @Security("is_granted(constant('\\Main\\PasswordBundle\\Security\\PasswordGroupVoter::VIEW'), passwordGroup)")
     *
     * @return View
     */
    public function cgetPasswordsAction(Request $request, PasswordGroup $passwordGroup)
    {
        $passwordManager = $this->get('main_password.services.password_manager');

        $queryBuilder = $passwordManager->qbAllPasswordsByPasswordGroupAndUser($passwordGroup, $this->getUser());

        $filterManager = $this->get('uql.query_builder_operation.services.filter_manager');
        $apiManager = $this->get('uniquelibs.api_bundle.services.api_manager');

        return $apiManager->formatQueryBuilder(
            $request,
            $filterManager->executeRequest($request, $queryBuilder, $this->get('main_password.query_builder_mapper.password')),
            'api_get_passwordgroups_passwords',
            ['passwordGroup' => $passwordGroup->getId()]
        )->setContext($this->getContextByUser($this->getUser(), ['ShowAccess']));
    }
}
