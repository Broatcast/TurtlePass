<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new FOS\UserBundle\FOSUserBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),

            new Ambta\DoctrineEncryptBundle\AmbtaDoctrineEncryptBundle(),

            new Dunglas\AngularCsrfBundle\DunglasAngularCsrfBundle(),

            new JMS\SerializerBundle\JMSSerializerBundle(),

            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),

            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),

            new UniqueLibs\ApiBundle\UniqueLibsApiBundle(),
            new UniqueLibs\QueryBuilderOperationBundle\UniqueLibsQueryBuilderOperationBundle(),
            new UniqueLibs\FOSUserRecaptchaProtectionBundle\UniqueLibsFOSUserRecaptchaProtectionBundle(),

            new Main\AppBundle\AppBundle(),
            new Main\ApiBundle\MainApiBundle(),
            new Main\TemplateBundle\MainTemplateBundle(),
            new Main\UserBundle\MainUserBundle(),
            new Main\PasswordBundle\MainPasswordBundle(),
            new Main\LanguageBundle\MainLanguageBundle(),
            new Main\LdapBundle\MainLdapBundle(),
            new Main\FOSUserRecaptchaProtectionBundle\MainFOSUserRecaptchaProtectionBundle(),
        ];

        if (extension_loaded('ldap') && file_exists($this->getRootDir() . '/config/config_ldap.yml')) {
            $bundles[] = new FR3D\LdapBundle\FR3DLdapBundle();
        }

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('container.autowiring.strict_mode', false);
            $container->setParameter('container.dumper.inline_class_loader', true);

            $container->addObjectResource($this);
        });

        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');

        if (extension_loaded('ldap') && file_exists($this->getRootDir() . '/config/config_ldap.yml')) {
            $loader->load($this->getRootDir() . '/config/security_ldap.yml');
            $loader->load($this->getRootDir() . '/config/config_ldap.yml');
        } else {
            $loader->load($this->getRootDir() . '/config/security.yml');
        }
    }
}
