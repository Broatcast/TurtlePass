<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Configuration\ConfigurationManager;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLogin;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class ProtectionManager
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var InvalidLoginManager
     */
    protected $invalidLoginManager;

    /**
     * @var LoginAccessManager
     */
    protected $loginAccessManager;

    /**
     * @param ConfigurationManager $configurationManager
     * @param InvalidLoginManager  $invalidLoginManager
     * @param LoginAccessManager   $loginAccessManager
     */
    public function __construct(ConfigurationManager $configurationManager, InvalidLoginManager $invalidLoginManager, LoginAccessManager $loginAccessManager)
    {
        $this->configurationManager = $configurationManager;
        $this->invalidLoginManager = $invalidLoginManager;
        $this->loginAccessManager = $loginAccessManager;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function showRecaptchaOnLoginPage(Request $request)
    {
        // Do not show recaptcha if keys are not defined
        if (!$this->configurationManager->getRecaptchaPrivateKey() || !$this->configurationManager->getRecaptchaSiteKey()) {
            return false;
        }

        // Show recaptcha if recaptcha_needed_fails is set to zero
        if (!$this->configurationManager->getRecaptchaNeededFails()) {
            return true;
        }

        // Do not show recaptcha if ip address is whitelisted
        if ($this->isWhitelisted($request)) {
            return false;
        }

        $invalidLogin = $this->invalidLoginManager->getInvalidLoginByIpAddress($request->getClientIp());

        if ($invalidLogin instanceof InvalidLogin) {
            if ($invalidLogin->getCount() >= $this->configurationManager->getRecaptchaNeededFails()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function showRecaptchaOnResetPasswordPage(Request $request)
    {
        // Do not show recaptcha if keys are not defined
        if (!$this->configurationManager->getRecaptchaPrivateKey() || !$this->configurationManager->getRecaptchaSiteKey()) {
            return false;
        }

        // Do not show recaptcha if ip address is whitelisted
        if ($this->isWhitelisted($request)) {
            return false;
        }

        // Only show recaptcha if recaptcha_on_password_reset is true (default)
        if ($this->configurationManager->isRecaptchaOnPasswordReset()) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function showRecaptchaOnRegistration(Request $request)
    {
        // Do not show recaptcha if keys are not defined
        if (!$this->configurationManager->getRecaptchaPrivateKey() || !$this->configurationManager->getRecaptchaSiteKey()) {
            return false;
        }

        // Do not show recaptcha if ip address is whitelisted
        if ($this->isWhitelisted($request)) {
            return false;
        }

        // Only show recaptcha if recaptcha_on_password_reset is true (default)
        if ($this->configurationManager->isRecaptchaOnRegistration()) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isLoginBlocked(Request $request)
    {
        if ($this->isWhitelisted($request)) {
            return false;
        }

        $invalidLogin = $this->invalidLoginManager->getInvalidLoginByIpAddress($request->getClientIp());

        if ($invalidLogin instanceof InvalidLogin) {
            if ($invalidLogin->getCount() >= $this->configurationManager->getAllowedRetries()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isBlacklisted(Request $request)
    {
        return $this->loginAccessManager->isIpAddressBlacklisted($request->getClientIp());
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isWhitelisted(Request $request)
    {
        return $this->loginAccessManager->isIpAddressWhitelisted($request->getClientIp());
    }

    /**
     * @param Request $request
     */
    public function loginFailed(Request $request)
    {
        $this->invalidLoginManager->updateFail($request->getClientIp());
    }

    /**
     * @return ConfigurationManager
     */
    public function getConfigurationManager()
    {
        return $this->configurationManager;
    }
}