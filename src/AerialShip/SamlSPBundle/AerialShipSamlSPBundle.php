<?php

namespace AerialShip\SamlSPBundle;

use AerialShip\SamlSPBundle\DependencyInjection\Security\Factory\SamlSpFactory;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AerialShipSamlSPBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SamlSpFactory());

        $this->addRegisterMappingsPass($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        if (false === class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass')) {
            throw new \LogicException('Missing RegisterMappingsPass available since symfony 2.3');
        }

        $mappings = array(realpath(__DIR__ . '/Resources/config/doctrine-mapping') => 'AerialShip\SamlSPBundle\Model',);

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('aerial_ship_saml_sp.model_manager_name'), 'aerial_ship_saml_sp.backend_type_orm'));
        }

        if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, array('aerial_ship_saml_sp.model_manager_name'), 'aerial_ship_saml_sp.backend_type_mongodb'));
        }
    }
}
