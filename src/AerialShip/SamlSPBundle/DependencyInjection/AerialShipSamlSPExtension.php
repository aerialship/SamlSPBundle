<?php

namespace AerialShip\SamlSPBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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

        $this->loadEntityDescriptorProvider('sp', $config, $container);
        $this->loadEntityDescriptorProvider('idp', $config, $container);

        if (isset($config['sp_meta']['id'])) {
            $container->setAlias('aerial_ship_saml_sp.sp_meta_provider', $config['sp_meta']['id']);
        } else if (isset($config['sp_meta']['config'])) {
            $container->setDefinition(
                'aerial_ship_saml_sp.sp_meta_provider',
                new Definition(
                    $container->getParameter('aerial_ship_saml_sp.sp_meta_provider.class'),
                    array($config['sp_meta']['config'])
                )
            );
        } else {
            throw new \RuntimeException('aerial_ship_saml_sp.sp_meta has to have either id or config');
        }
    }


    protected function loadEntityDescriptorProvider($type, array $config, ContainerBuilder $container)
    {
        if (isset($config['entity_descriptor'][$type]['id'])) {
            $container->setAlias('aerial_ship_saml_sp.entity_descriptor_provider.'.$type, $config['entity_descriptor'][$type]['id']);
        } else {
            $class = $container->getParameter('aerial_ship_saml_sp.entity_descriptor_provider.'.$type.'.class');
            $provider = $container->setDefinition(
                'aerial_ship_saml_sp.entity_descriptor_provider.'.$type,
                new Definition($class, array(new Reference('kernel')))
            );
            if (isset($config['entity_descriptor'][$type]['file'])) {
                $file = $config['entity_descriptor'][$type]['file'];
                $provider->addMethodCall('setFilename', array($file));
            }
        }
    }
} 