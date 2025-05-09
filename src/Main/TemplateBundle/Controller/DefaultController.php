<?php

namespace Main\TemplateBundle\Controller;

use Google\Authenticator\GoogleAuthenticator;
use Main\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->hasSecret() && $request->getSession()->get('2factor') !== true) {
            return $this->redirectToRoute('main_template_default_authenticate');
        }

        $sessionTokenManager = $this->get('main_api.services.session_token_manager');
        $languageManager = $this->get('main_language.services.language_manager');

        return $this->render('MainTemplateBundle:Default:index.html.twig', [
            'user' => $this->getUser(),
            'token' => $sessionTokenManager->getToken(),
            'version' => $this->getParameter('app_version'),
            'languages' => $languageManager->getAllLanguages(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/authenticate")
     */
    public function authenticateAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->hasSecret() || $request->getSession()->get('2factor') === true) {
            return $this->redirectToRoute('main_template_default_authenticate');
        }

        $form = $this->createFormBuilder()
            ->add('code', TextType::class, [
                'label' => '2 Factor Code',
                'translation_domain' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Check Code',
                'translation_domain' => false,
                'attr' => [
                    'class' => 'btn btn-login btn-block'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $authenticator = new GoogleAuthenticator();

            if ($authenticator->checkCode($user->getSecret(), $form->get('code')->getData())) {
                $request->getSession()->set('2factor', true);

                return $this->redirectToRoute('main_template_default_index');
            }

            $form->get('code')->addError(new FormError('Invalid Code'));
        }

        return $this->render('MainTemplateBundle:Authenticate:index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
