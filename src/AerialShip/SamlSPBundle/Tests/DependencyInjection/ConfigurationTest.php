<?php

namespace AerialShip\SamlSPBundle\Tests\DependencyInjection;

use AerialShip\SamlSPBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function shouldRequireSSOStateEntityClass()
    {
        $configs = array();
        $this->processConfiguration($configs);
    }


    /**
     * @test
     */
    public function shouldAllowUsageWithSSOStateEntityClassOnly()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'sso_state_entity_class' => 'Some\Class'
        ));
        $this->processConfiguration($configs);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     */
    public function throwIfNonScalarSSOStateEntityClass()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'sso_state_entity_class' => array()
        ));
        $this->processConfiguration($configs);
    }


    /**
     * @test
     */
    public function shouldAllowOrmDbDriver()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'driver' => 'orm',
            'sso_state_entity_class' => 'Some\Class'
        ));
        $this->processConfiguration($configs);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function throwIfUnknownDbDriver()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'driver' => 'something_unknown',
            'sso_state_entity_class' => 'Some\Class'
        ));
        $this->processConfiguration($configs);
    }


    /**
     * @test
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     */
    public function throwIfNonScalarDbDriver()
    {
        $configs = array('aerial_ship_saml_sp' => array(
            'driver' => array(),
            'sso_state_entity_class' => 'Some\Class'
        ));
        $this->processConfiguration($configs);
    }





    protected function processConfiguration(array $configs)
    {
        $configuration = new Configuration();
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}
