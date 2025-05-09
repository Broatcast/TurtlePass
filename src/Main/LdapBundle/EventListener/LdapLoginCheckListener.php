<?php

namespace Main\LdapBundle\EventListener;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Services\SettingManager;
use Main\UserBundle\Entity\LdapUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LdapLoginCheckListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @param RouterInterface       $router
     * @param TranslatorInterface   $translator
     * @param TokenStorageInterface $tokenStorage
     * @param SettingManager        $settingManager
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, TokenStorageInterface $tokenStorage, SettingManager $settingManager)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->settingManager = $settingManager;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->settingManager->getSetting(Setting::ID_LDAP_USER_PROFILE_FULFILLMENT_REQUIREMENT)->getValue() == '0') {
            return;
        }

        $request = $event->getRequest();
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        $token = $this->tokenStorage->getToken();

        $route = $this->router->match($request->getPathInfo());

        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            if ($user instanceof LdapUser) {
                $email = $user->getEmail();
                if (empty($email) && is_array($route) && isset($route['_route']) && $route['_route'] !== 'main_ldap_profile_index') {
                    $session->set('fulfill_profile_required', true);

                    $event->setResponse(new RedirectResponse($this->router->generate('main_ldap_profile_index')));
                    $event->stopPropagation();
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest')),
        );
    }
}