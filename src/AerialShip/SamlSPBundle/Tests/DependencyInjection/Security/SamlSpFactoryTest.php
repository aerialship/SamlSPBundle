<?php

namespace AerialShip\SamlSPBundle\Tests\DependencyInjection\Security;

use AerialShip\SamlSPBundle\DependencyInjection\Security\Factory\SamlSpFactory;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
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
        $this->assertEquals('/saml/sp/login', $children['login_path']->getDefaultValue());
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
        $this->assertEquals('/saml/sp/acs', $children['check_path']->getDefaultValue());
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
        $this->assertEquals('/saml/sp/logout', $children['logout_path']->getDefaultValue());
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
        $this->assertEquals('/saml/sp/FederationMetadata.xml', $children['metadata_path']->getDefaultValue());
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
        $this->assertEquals('/saml/sp/discovery', $children['discovery_path']->getDefaultValue());
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
        $this->assertContainsOnly('string', $result);
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

        list($providerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);
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

        list(,$listenerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);
        $this->assertStringStartsWith('security.authentication.listener.aerial_ship_saml_sp', $listenerID);
        $this->assertStringEndsWith('.main', $listenerID);
    }

    /**
     * @test
     */
    public function shouldReturnFormEntryPointyWithPostfixId()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list(,,$entryPointID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);
        $this->assertStringStartsWith('security.authentication.form_entry_point', $entryPointID);
        $this->assertStringEndsWith('.main', $entryPointID);
    }

    /**
     * @test
     */
    public function shouldCreateRelayingPartyWithListenerSetterMethod()
    {
        $expectedRelyingPartyId = 'custom.relying_party.id';

        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        $config['relying_party'] = $expectedRelyingPartyId;

        list(,$listenerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);

        $this->assertTrue($containerBuilder->hasDefinition($listenerID));

        $listenerDefinition = $containerBuilder->getDefinition($listenerID);

        $methodCalls = $listenerDefinition->getMethodCalls();
        $this->assertCount(1, $methodCalls);
        $this->assertEquals('setRelyingParty', $methodCalls[0][0]);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $methodCalls[0][1][0]);
        $this->assertEquals($expectedRelyingPartyId, (string) $methodCalls[0][1][0]);
    }

    /**
     * @test
     */
    public function shouldCreateSpEntityDescriptorBuilder()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.sp_entity_descriptor_builder.main.aaa'));
    }

    /**
     * @test
     */
    public function shouldCreateMetaProviderCollection()
    {
        $expectedIDPProvider = 'custom.idp.ed.provider';
        $expectedSPProvider = 'custom.sp.meta.provider';

        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->getCommonConfiguration();

        $config['services']['bbb']['idp']['id'] = $expectedIDPProvider;
        $config['services']['bbb']['sp']['meta']['id'] = $expectedSPProvider;

        $config = $configProcessor->processConfiguration($config);
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.service_info_collection.main'));
        $metaProvidersDefinition = $containerBuilder->getDefinition('aerial_ship_saml_sp.service_info_collection.main');

        $methodCalls = $metaProvidersDefinition->getMethodCalls();
        $this->assertCount(2, $methodCalls);
        $this->assertEquals('add', $methodCalls[0][0]);
        $this->assertEquals('add', $methodCalls[1][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $methodCalls[0][1][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $methodCalls[1][1][0]);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.service_info.main.aaa'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.service_info.main.bbb'));

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.idp_entity_descriptor_provider.main.aaa'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.sp_meta_provider.main.aaa'));

        $this->assertFalse($containerBuilder->hasDefinition('aerial_ship_saml_sp.idp_entity_descriptor_provider.main.bbb'));
        $this->assertFalse($containerBuilder->hasDefinition('aerial_ship_saml_sp.sp_meta_provider.main.bbb'));
    }


    /**
     * @test
     */
    public function shouldCreateRequestStateStore()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.state.store.request.main'));
    }

    /**
     * @test
     */
    public function shouldCreateRelyingParties()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.discovery.main'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.federation_metadata.main'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.authenticate.main'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.assertion_consumer.main'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.logout.main'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.sso_session_check.main'));
        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.relying_party.composite.main'));
    }


    /**
     * @test
     */
    public function shouldSetOnlyProviderKeyToAuthenticationProviderIfProviderNotSetInConfig()
    {
        $expectedProviderKey = 'some_provider_key';

        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->processCommonConfiguration();
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list($providerID) = $factory->create($containerBuilder, $expectedProviderKey, $config, 'user.provider.id', null);

        $this->assertTrue($containerBuilder->hasDefinition($providerID));
        $providerDefinition = $containerBuilder->getDefinition($providerID);
        $this->assertEquals($expectedProviderKey, $providerDefinition->getArgument(0));
    }

    /**
     * @test
     */
    public function shouldSetProviderKeyUserProviderAdapterAndUserCheckerIfProviderSetInConfig()
    {
        $expectedProviderKey = 'some_provider_key';
        $expectedUserProvider = 'user.provider.id';

        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->getCommonConfiguration();
        $config['provider'] = $expectedUserProvider;
        $config = $configProcessor->processConfiguration($config);
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list($providerID) = $factory->create($containerBuilder, $expectedProviderKey, $config, $expectedUserProvider, null);

        $this->assertTrue($containerBuilder->hasDefinition($providerID));

        $providerDefinition = $containerBuilder->getDefinition($providerID);
        $this->assertCount(4, $providerDefinition->getArguments());

        $this->assertEquals($expectedProviderKey, $providerDefinition->getArgument(0));

        $adapterReference = $providerDefinition->getArgument(1);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $adapterReference);
        $this->assertEquals('aerial_ship_saml_sp.user_provider_adapter.'.$expectedProviderKey, (string)$adapterReference);

        $this->assertTrue($containerBuilder->hasDefinition('aerial_ship_saml_sp.user_provider_adapter.'.$expectedProviderKey));
        $adapterDefinition = $containerBuilder->getDefinition('aerial_ship_saml_sp.user_provider_adapter.'.$expectedProviderKey);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $adapterDefinition->getArgument(0));
        $this->assertEquals($expectedUserProvider, (string)$adapterDefinition->getArgument(0));

        $checkerReference = $providerDefinition->getArgument(2);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $checkerReference);
        $this->assertEquals('security.user_checker', (string)$checkerReference);
    }


    /**
     * @test
     */
    public function shouldSetCreateUserIfNotExistsToAuthenticationProviderWhenTrue()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->getCommonConfiguration();
        $config['create_user_if_not_exists'] = true;
        $config = $configProcessor->processConfiguration($config);
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list($providerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);
        $providerDefinition = $containerBuilder->getDefinition($providerID);

        $this->assertTrue($providerDefinition->getArgument(3));
    }


    /**
     * @test
     */
    public function shouldSetCreateUserIfNotExistsToAuthenticationProviderWhenFalse()
    {
        $factory = new SamlSpFactory();
        $configProcessor = new SamlSpFactoryConfiguration($factory, 'name');
        $config = $configProcessor->getCommonConfiguration();
        $config['create_user_if_not_exists'] = false;
        $config = $configProcessor->processConfiguration($config);
        $containerBuilder = new ContainerBuilder(new ParameterBag());

        list($providerID) = $factory->create($containerBuilder, 'main', $config, 'user.provider.id', null);
        $providerDefinition = $containerBuilder->getDefinition($providerID);

        $this->assertFalse($providerDefinition->getArgument(3));
    }
} 
