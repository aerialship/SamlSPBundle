<?php

namespace AerialShip\SamlSPBundle\Tests\DependencyInjection;

use AerialShip\SamlSPBundle\DependencyInjection\AerialShipSamlSPExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;


class AerialShipSamlSPExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldLoadExtensionWithSSOStateEntityClassOnly()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'sso_state_entity_class' => 'AerialShip\SamlSPBundle\Tests\DependencyInjection\TestSSOStateEntity'
        ));
        $extension = new AerialShipSamlSPExtension();
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.state.store.sso'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfInvalidSSOStateEntityClass()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'sso_state_entity_class' => 'Some\Non\Existing\Class'
        ));
        $extension = new AerialShipSamlSPExtension();
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $extension->load($configs, $containerBuilder);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function throwIfNoSSOStateEntityClass()
    {
        $configs = array('aerial_ship_saml_sp' => array());
        $extension = new AerialShipSamlSPExtension();
        $containerBuilder = new ContainerBuilder(new ParameterBag());
        $extension->load($configs, $containerBuilder);
    }



} 