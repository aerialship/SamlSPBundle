<?php

namespace AerialShip\SamlSPBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AerialShipSamlSPExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // name of doctrine object manager to use, defaults to null, which resolves to default object manager - used in mappings compiler pass
        $container->setParameter('aerial_ship_saml_sp.model_manager_name', $config['model_manager_name']);

        $ssoStateEntityClass = $config['sso_state_entity_class'];
        if (!class_exists($ssoStateEntityClass)) {
            throw new \InvalidArgumentException(sprintf(
                'The option `%s` contains %s but it is not a valid class name.',
                'sso_state_entity_class',
                $ssoStateEntityClass
            ));
        }
        $container->setParameter('aerial_ship_saml_sp.state.store.sso.entity_class', $ssoStateEntityClass);
        $loader->load($config['driver'].'.yml');

        // if doctrine driver, replace the manager definition so doctrine will provide it
        if (in_array($config['driver'], array('orm', 'mongodb'))) {
            if ('orm' === $config['driver']) {
                $managerService = 'aerial_ship_saml_sp.entity_manager';
                $doctrineService = 'doctrine';
            } else {
                $managerService = 'aerial_ship_saml_sp.document_manager';
                $doctrineService = sprintf('doctrine_%s', $config['driver']);
            }
            $definition = $container->getDefinition($managerService);
            if (method_exists($definition, 'setFactory')) {
                $definition->setFactory(array(new Reference($doctrineService), 'getManager'));
            } else {
                $definition->setFactoryService($doctrineService);
                $definition->setFactoryMethod('getManager');
            }
        }
    }
}
