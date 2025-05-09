<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UniqueLibsFOSUserRecaptchaProtectionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.invalid_login_class', $config['invalid_login_class']);
        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.login_access_class', $config['login_access_class']);

        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.recaptcha_site_key', isset($config['recaptcha_site_key']) ? $config['recaptcha_site_key'] : null);
        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.recaptcha_private_key', isset($config['recaptcha_private_key']) ? $config['recaptcha_private_key'] : null);
        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.recaptcha_needed_fails', isset($config['recaptcha_needed_fails']) ? (int)$config['recaptcha_needed_fails'] : 3);
        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.recaptcha_on_password_reset', (!isset($config['recaptcha_on_password_reset']) || $config['recaptcha_on_password_reset'] || $config['recaptcha_on_password_reset'] === 'true') ? true : false);
        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.recaptcha_on_registration', (!isset($config['recaptcha_on_registration']) || $config['recaptcha_on_registration'] || $config['recaptcha_on_registration'] === 'true') ? true : false);

        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.allowed_retries', isset($config['allowed_retries']) ? (int)$config['allowed_retries'] : 10);

        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.lock_time', isset($config['lock_time']) ? $config['lock_time'] : 'P1D');

        $container->setParameter('uqe.fos_user_recaptcha_protection_bundle.allow_only_whitelisted', (isset($config['allow_only_whitelisted']) && $config['allow_only_whitelisted'] && $config['allow_only_whitelisted'] !== 'false') ? true : false);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
