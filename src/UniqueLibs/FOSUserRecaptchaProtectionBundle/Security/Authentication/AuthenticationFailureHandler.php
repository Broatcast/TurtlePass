<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Security\Authentication;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Services\ProtectionManager;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var ProtectionManager
     */
    protected $protectionManager;

    /**
     * AuthenticationFailureHandler constructor.
     *
     * @param HttpKernelInterface  $httpKernel
     * @param HttpUtils            $httpUtils
     * @param array                $options
     * @param LoggerInterface|null $logger
     * @param ProtectionManager    $protectionManager
     */
    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null, ProtectionManager $protectionManager)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);

        $this->protectionManager = $protectionManager;
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->protectionManager->loginFailed($request);

        return parent::onAuthenticationFailure($request, $exception);
    }
}