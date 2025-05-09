<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle;

use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
class UniqueLibsFOSUserRecaptchaProtectionBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->addNewRegisterMappingsPass($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addNewRegisterMappingsPass(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'UniqueLibs\FOSUserRecaptchaProtectionBundle\Model',
        );

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
        }

        if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings));
        }

        if (class_exists('Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass')) {
            $container->addCompilerPass(DoctrineCouchDBMappingsPass::createXmlMappingDriver($mappings));
        }
    }
}
