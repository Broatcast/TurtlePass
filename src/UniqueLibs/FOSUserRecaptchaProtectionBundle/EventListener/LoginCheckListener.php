<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\EventListener;

use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Exceptions\LoginFailedException;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Services\ProtectionManager;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class LoginCheckListener implements EventSubscriberInterface
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
     * @var ProtectionManager
     */
    protected $protectionManager;

    /**
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param ProtectionManager   $protectionManager
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator, ProtectionManager $protectionManager)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->protectionManager = $protectionManager;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $route = $this->router->match($request->getPathInfo());

        if (isset($route) && is_array($route) && isset($route['_route']) && $route['_route'] == 'fos_user_security_check') {

            try {
                if ($this->protectionManager->getConfigurationManager()->isAllowOnlyWhitelisted()) {
                    if (!$this->protectionManager->isWhitelisted($request)) {
                        throw new LoginFailedException('protection.ip_address_not_whitelisted');
                    }
                }

                if ($this->protectionManager->isBlacklisted($request)) {
                    throw new LoginFailedException('protection.ip_address_blacklisted');
                }

                if ($this->protectionManager->isLoginBlocked($request)) {
                    throw new LoginFailedException('protection.login_fail_limit_exceeded');
                }

                if ($this->protectionManager->showRecaptchaOnLoginPage($request)) {
                    $captcha = new ReCaptcha($this->protectionManager->getConfigurationManager()->getRecaptchaPrivateKey());

                    $success = $captcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess();

                    if ($success !== true) {
                        throw new LoginFailedException('recaptcha.invalid');
                    }
                }
            } catch (LoginFailedException $exception) {

                $event->getRequest()->getSession()->getFlashBag()->add('error', $this->translator->trans($exception->getMessage(), array(), 'UniqueLibsFOSUserRecaptchaProtectionBundle'));

                $redirectResponse = new RedirectResponse($this->router->generate('fos_user_security_login'));
                $event->setResponse($redirectResponse);
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 18)),
        );
    }
}
