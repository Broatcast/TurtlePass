<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('unique_libs_fos_user_recaptcha_protection');

        $rootNode
            ->children()
            ->scalarNode('invalid_login_class')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('login_access_class')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('recaptcha_site_key')->end()
            ->scalarNode('recaptcha_private_key')->end()
            ->scalarNode('recaptcha_needed_fails')->end()
            ->scalarNode('recaptcha_on_password_reset')->end()
            ->scalarNode('recaptcha_on_registration')->end()
            ->scalarNode('lock_time')->end()
            ->scalarNode('allowed_retries')->end()
            ->scalarNode('allow_only_whitelisted')->end()
            ->end();

        return $treeBuilder;
    }
}
