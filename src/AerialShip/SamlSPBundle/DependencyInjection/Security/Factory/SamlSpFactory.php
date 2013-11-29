<?php

namespace AerialShip\SamlSPBundle\DependencyInjection\Security\Factory;

use AerialShip\SamlSPBundle\Config\MetaProvider;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;


class SamlSpFactory extends AbstractFactory
{

    function __construct() {
        $this->defaultSuccessHandlerOptions['login_path'] = '/saml/login';
        $this->defaultFailureHandlerOptions['login_path'] = '/saml/login';
        $this->defaultFailureHandlerOptions['failure_path'] = '/saml/failure';

        // these are available in listener->options[]
        $this->addOption('login_path', '/saml/login');
        $this->addOption('check_path', '/saml/login_check');
        $this->addOption('logout_path', '/saml/logout');
        $this->addOption('failure_path', '/saml/failure');
        $this->addOption('target_path_parameter', $this->defaultSuccessHandlerOptions['target_path_parameter']);
    }

    public function addConfiguration(NodeDefinition $node) {
        parent::addConfiguration($node);
        $node->children()
            ->arrayNode('sp')
                ->children()
                    ->scalarNode('entity_id')->cannotBeEmpty()->isRequired()->end()
                    ->booleanNode('want_assertions_signed')->cannotBeEmpty()->defaultTrue()->end()
                ->end()
            ->end()
            ->scalarNode('relying_party')->defaultValue('aerial_ship_saml_sp.relying_party.default')->cannotBeEmpty()->end()
            ->scalarNode('login_path')->defaultValue('/saml/login')->cannotBeEmpty()->end()
            ->scalarNode('check_path')->defaultValue('/saml/acs')->cannotBeEmpty()->end()
            ->scalarNode('logout_path')->defaultValue('/saml/logout')->cannotBeEmpty()->end()
            ->scalarNode('logout_receive_path')->defaultValue('/saml/logout_receive')->cannotBeEmpty()->end()
            ->scalarNode('failure_path')->defaultValue('/saml/failure')->cannotBeEmpty()->end()
            ->scalarNode('metadata_path')->defaultValue('/saml/FederationMetadata.xml')->cannotBeEmpty()->end()
            ->scalarNode('discovery_path')->defaultValue('/saml/discovery')->cannotBeEmpty()->end()
            ->scalarNode('provider')->defaultValue('aerial_ship_saml_sp.user_provider.default')->cannotBeEmpty()->end()
            ->arrayNode('services')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->prototype('array')
                    ->children()
                        ->arrayNode('idp')->isRequired()
                            ->children()
                                ->scalarNode('file')->end()
                                ->scalarNode('id')->end()
                            ->end()
                        ->end()
                        ->arrayNode('sp')->isRequired()
                            ->children()
                                ->scalarNode('id')->end()
                                ->enumNode('name_id_format')
                                    ->values(array('persistent', 'transient'))
                                    ->cannotBeEmpty()
                                    ->defaultValue('persistent')
                                ->end()
                                ->arrayNode('binding')->isRequired()
                                    ->children()
                                        ->enumNode('authn_request')
                                            ->values(array('redirect', 'post'))
                                            ->defaultValue('redirect')
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->enumNode('logout_request')
                                            ->values(array('redirect', 'post'))
                                            ->defaultValue('redirect')
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }


    protected function createListener($container, $id, $config, $userProvider) {
        $this->addOption('login_path', $config['login_path']);
        $this->addOption('check_path', $config['check_path']);
        $this->addOption('logout_path', $config['logout_path']);
        $this->addOption('logout_receive_path', $config['logout_receive_path']);
        $this->addOption('failure_path', $config['failure_path']);
        $this->addOption('metadata_path', $config['metadata_path']);
        $this->addOption('discovery_path', $config['discovery_path']);

        $this->createSpEntityDescriptorBuilder($container, $id, $config);
        $this->createMetaProviders($container, $id, $config);
        $this->createStateStores($container, $id, $config);
        $this->createRelyingParties($container, $id, $config);

        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        $container
                ->getDefinition($listenerId)
                ->addMethodCall('setRelyingParty', array(new Reference('aerial_ship_saml_sp.relying_party.composite.'.$id)))
        ;

        return $listenerId;
    }


    protected function createSpEntityDescriptorBuilder(ContainerBuilder $container, $id, array $config) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.sp_entity_descriptor_builder');
        $service->replaceArgument(0, $config);
        $container->setDefinition("aerial_ship_saml_sp.sp_entity_descriptor_builder.{$id}", $service);
    }


    protected function createMetaProviders(ContainerBuilder $container, $id, array $config) {
        $collection = new DefinitionDecorator('aerial_ship_saml_sp.meta.provider_collection');
        $container->setDefinition("aerial_ship_saml_sp.meta.provider_collection.{$id}", $collection);
        foreach ($config['services'] as $name=>$meta)
        {
            if (isset($meta['idp']['id'])) {
                $idp = new Reference($meta['idp']['id']);
            } else {
                $idpService = new DefinitionDecorator('aerial_ship_saml_sp.entity_descriptor_provider.idp');
                $container->setDefinition("aerial_ship_saml_sp.entity_descriptor_provider.idp.{$id}", $idpService);
                if (isset($meta['idp']['file'])) {
                    $idpService->addMethodCall('setFilename', array($meta['idp']['file']));
                }
                $idp = new Reference("aerial_ship_saml_sp.entity_descriptor_provider.idp.{$id}");
            }

            if (isset($meta['sp']['id'])) {
                $spMeta = new Reference($meta['sp']['id']);
            } else {
                $spMetaService = new DefinitionDecorator('aerial_ship_saml_sp.sp_meta_provider');
                $spMetaService->replaceArgument(0, $meta['sp']);
                $container->setDefinition("aerial_ship_saml_sp.sp_meta_provider.{$id}.{$name}", $spMetaService);
                $spMeta = new Reference("aerial_ship_saml_sp.sp_meta_provider.{$id}.{$name}");
            }

            $provider = new DefinitionDecorator('aerial_ship_saml_sp.meta.provider');
            $provider->replaceArgument(0, $id);
            $provider->replaceArgument(1, $name);
            $provider->replaceArgument(2, $idp);
            $provider->replaceArgument(3, $spMeta);
            $container->setDefinition("aerial_ship_saml_sp.meta.provider.{$id}.{$name}", $provider);
            $collection->addMethodCall('add', array(new Reference("aerial_ship_saml_sp.meta.provider.{$id}.{$name}")));
        }
    }


    protected function createStateStores(ContainerBuilder $container, $id, array $config) {
        $this->createAuthnStore($container, $id, $config);
    }

    protected function createAuthnStore(ContainerBuilder $container, $id, array $config) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.state.store.authn');
        $service->replaceArgument(1, $id);
        $container->setDefinition('aerial_ship_saml_sp.state.store.authn.'.$id, $service);
    }

    protected function createRelyingParties(ContainerBuilder $container, $id, array $config) {
        $this->createRelyingPartyDiscovery($container, $id, $config);
        $this->createRelyingPartyFederationMetadata($container, $id, $config);
        $this->createRelyingPartyAuthenticate($container, $id);
        $this->createRelyingPartyAssertionConsumer($container, $id);
        $this->createRelyingPartyComposite($container, $id);
    }

    protected function createRelyingPartyDiscovery(ContainerBuilder $container, $id, array $config) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.discovery');
        $service->replaceArgument(1, new Reference("aerial_ship_saml_sp.meta.provider_collection.{$id}"));
        $container->setDefinition("aerial_ship_saml_sp.relying_party.discovery.{$id}", $service);
    }

    protected function createRelyingPartyFederationMetadata(ContainerBuilder $container, $id, array $config) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.federation_metadata');
        $service->replaceArgument(0, new Reference('aerial_ship_saml_sp.sp_entity_descriptor_builder.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.federation_metadata.'.$id, $service);
    }

    protected function createRelyingPartyAuthenticate(ContainerBuilder $container, $id) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.authenticate');
        $service->replaceArgument(0, new Reference('aerial_ship_saml_sp.sp_entity_descriptor_builder.'.$id));
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.meta.provider_collection.'.$id));
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.state.store.authn.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.authenticate.'.$id, $service);
    }

    protected function createRelyingPartyAssertionConsumer(ContainerBuilder $container, $id) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.assertion_consumer');
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.meta.provider_collection.'.$id));
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.state.store.authn.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.assertion_consumer.'.$id, $service);
    }

    protected function createRelyingPartyComposite(ContainerBuilder $container, $id) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.composite');
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.discovery.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.federation_metadata.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.authenticate.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.assertion_consumer.'.$id)));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.composite.'.$id, $service);
    }


    /**
     * Subclasses must return the id of a service which implements the
     * AuthenticationProviderInterface.
     *
     * @param ContainerBuilder $container
     * @param string $id The unique id of the firewall
     * @param array $config The options array for this listener
     * @param string $userProviderId The id of the user provider
     *
     * @return string never null, the id of the authentication provider
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId) {
        $providerId = 'security.authentication.provider.aerial_ship_saml_sp.'.$id;
        $provider = $container
                ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.aerial_ship_saml_sp'))
                ->replaceArgument(0, $id);

        // with user provider
        if (isset($config['provider'])) {
            $provider
                    ->addArgument(new Reference($userProviderId))
                    ->addArgument(new Reference('security.user_checker'))
            ;
        }

        return $providerId;
    }


    /**
     * Subclasses must return the id of the listener template.
     *
     * Listener definitions should inherit from the AbstractAuthenticationListener
     * like this:
     *
     *    <service id="my.listener.id"
     *             class="My\Concrete\Classname"
     *             parent="security.authentication.listener.abstract"
     *             abstract="true" />
     *
     * In the above case, this method would return "my.listener.id".
     *
     * @return string
     */
    protected function getListenerId() {
        return 'security.authentication.listener.aerial_ship_saml_sp';
    }

    public function getPosition() {
        return 'form';
    }

    public function getKey() {
        return 'aerial_ship_saml_sp';
    }

} 