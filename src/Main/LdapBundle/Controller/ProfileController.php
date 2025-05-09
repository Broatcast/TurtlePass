<?php

namespace Main\LdapBundle\Controller;

use Main\UserBundle\Entity\User;
use Main\UserBundle\Form\Type\EditOwnUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProfileController extends Controller
{
    /**
     * @Route("/fulfill_profile")
     *
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $token = $this->get('security.token_storage')->getToken();

        if ($token instanceof TokenInterface && $token->getUser() instanceof User) {
            $user = $token->getUser();
            $form = $this->createForm(EditOwnUserType::class, $user, [
                'method' => 'POST',
                'action' => $this->generateUrl('main_ldap_profile_index')
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->get('main_user.services.user_manager')->updateUser($user);

                $this->get('session')->set('fulfill_profile_required', false);

                return new RedirectResponse($this->generateUrl('main_template_default_index'));
            }

            return $this->render('MainLdapBundle:Profile:index.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        return new RedirectResponse($this->generateUrl('login'));
    }
}