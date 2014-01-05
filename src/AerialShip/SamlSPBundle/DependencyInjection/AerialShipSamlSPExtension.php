<?php

namespace AerialShip\SamlSPBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class AerialShipSamlSPExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

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
    }

}