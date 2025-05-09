<?php

namespace Main\FOSUserRecaptchaProtectionBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Main\AppBundle\Entity\Setting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Services\ProtectionManager;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Controller\RegistrationController as BaseRegistrationController;

class RegistrationController extends BaseRegistrationController
{
    protected $eventDispatcher;
    protected $formFactory;
    protected $userManager;
    protected $tokenStorage;

    public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, UserManagerInterface $userManager, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($eventDispatcher, $formFactory, $userManager, $tokenStorage);

        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function registerAction(Request $request)
    {
        $settingManager = $this->container->get('main_app.services.setting_manager');

        if ($settingManager->getSetting(Setting::ID_ENABLE_REGISTRATION)->getValue() == '0') {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        /** @var ProtectionManager $protectionManager */
        $protectionManager = $this->container->get('uqe.fos_user_recaptcha_protection_bundle.protection_manager');

        $recaptchaSiteKey = null;
        if ($protectionManager->showRecaptchaOnRegistration($request)) {
            $recaptchaSiteKey = $protectionManager->getConfigurationManager()->getRecaptchaSiteKey();
        }

        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $success = true;
            if ($protectionManager->showRecaptchaOnRegistration($request)) {
                $captcha = new ReCaptcha($protectionManager->getConfigurationManager()->getRecaptchaPrivateKey());

                $success = $captcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess();

                if ($success !== true) {

                    /** @var TranslatorInterface $translator */
                    $translator = $this->container->get('translator');

                    //$this->setFlash('error', $translator->trans('recaptcha.invalid', array(), 'UniqueLibsFOSUserRecaptchaProtectionBundle'));
                }
            }

            if ($form->isValid() && $success) {
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $this->userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('@FOSUser/Registration/register.html.twig', array(
            'form' => $form->createView(),
            'uniquelibs_recaptcha_site_key' => $recaptchaSiteKey,
        ));
    }
}
