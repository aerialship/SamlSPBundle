<?php

namespace AerialShip\SamlSPBundle\Tests\DependencyInjection\Security;


use AerialShip\SamlSPBundle\DependencyInjection\Security\Factory\SamlSpFactory;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SamlSpFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new SamlSpFactory();
    }

    /**
     * @test
     */
    public function shouldAllowGetKey()
    {
        $factory = new SamlSpFactory();
        $this->assertEquals('aerial_ship_saml_sp', $factory->getKey());
    }


    /**
     * @test
     */
    public function shouldAllowGetPosition()
    {
        $factory = new SamlSpFactory();
        $this->assertEquals('form', $factory->getPosition());
    }

    /**
     * @test
     */
    public function shouldAddCreateIfNotExistToConfigurationWithDefaultFalse()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('create_user_if_not_exists', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\BooleanNode', $children['create_user_if_not_exists']);
        /** @var $node BooleanNode */
        $node = $children['create_user_if_not_exists'];
        $this->assertFalse($node->getDefaultValue());
    }


    /**
     * @test
     */
    public function shouldAddRelyingPartyToConfigurationWithDefaultValueNull()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('relying_party', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['relying_party']);
        $this->assertNull($children['relying_party']->getDefaultValue());
    }

    /**
     * @test
     */
    public function shouldAddLoginPathToConfigurationWithExpectedDefaultValue()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('login_path', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['login_path']);
        $this->assertEquals('/saml/login', $children['login_path']->getDefaultValue());
    }

    /**
     * @test
     */
    public function shouldAddCheckPathToConfigurationWithExpectedDefaultValue()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('check_path', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['check_path']);
        $this->assertEquals('/saml/acs', $children['check_path']->getDefaultValue());
    }

    /**
     * @test
     */
    public function shouldAddLogoutPathToConfigurationWithExpectedDefaultValue()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('logout_path', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['logout_path']);
        $this->assertEquals('/saml/logout', $children['logout_path']->getDefaultValue());
    }

    /**
     * @test
     */
    public function shouldAddLogoutReceivePathToConfigurationWithExpectedDefaultValue()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('logout_receive_path', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['logout_receive_path']);
        $this->assertEquals('/saml/logout_receive', $children['logout_receive_path']->getDefaultValue());
    }

    /**
     * @test
     */
    public function shouldAddMetadataPathToConfigurationWithExpectedDefaultValue()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('metadata_path', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['metadata_path']);
        $this->assertEquals('/saml/FederationMetadata.xml', $children['metadata_path']->getDefaultValue());
    }

    /**
     * @test
     */
    public function shouldAddDiscoveryPathToConfigurationWithExpectedDefaultValue()
    {
        $factory = new SamlSpFactory();
        $treeBuilder = new TreeBuilder();
        $factory->addConfiguration($treeBuilder->root('name'));
        /** @var $tree ArrayNode */
        $tree = $treeBuilder->buildTree();
        $children = $tree->getChildren();

        $this->assertArrayHasKey('discovery_path', $children);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['discovery_path']);
        $this->assertEquals('/saml/discovery', $children['discovery_path']->getDefaultValue());
    }


    /**
     * @test
     */
    public function shouldReturnArrayOfStrings()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        $result = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);

        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertInternalType('string', $result[0]);
        $this->assertInternalType('string', $result[1]);
        $this->assertNull($result[2]);
    }

    /**
     * @test
     */
    public function shouldReturnSamlSpProviderWithPostfixID()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list($providerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', '');
        $this->assertStringStartsWith('security.authentication.provider.aerial_ship_saml_sp', $providerID);
        $this->assertStringEndsWith('.main', $providerID);
    }

    /**
     * @test
     */
    public function shouldReturnSamlSpListenerWithPostfixID()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list(,$listenerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', '');
        $this->assertStringStartsWith('security.authentication.listener.aerial_ship_saml_sp', $listenerID);
        $this->assertStringEndsWith('.main', $listenerID);
    }

} 