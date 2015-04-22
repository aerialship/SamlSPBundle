<?php

namespace AerialShip\SamlSPBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class SamlSpFactory extends AbstractFactory
{

    function __construct()
    {
        $this->defaultSuccessHandlerOptions['login_path'] = '/saml/sp/login';
        $this->defaultFailureHandlerOptions['login_path'] = '/saml/sp/login';
        $this->defaultFailureHandlerOptions['failure_path'] = '/saml/sp/failure';

        // these are available in listener->options[]
        $this->addOption('require_previous_session', false); // otherwise it will end up with throw new SessionUnavailableException('Your session has timed out, or you have disabled cookies.'); on each new session
        $this->addOption('login_path', '/saml/sp/login');
        $this->addOption('check_path', '/saml/sp/acs');
        $this->addOption('logout_path', '/saml/sp/logout');
        $this->addOption('failure_path', '/saml/sp/failure');
        $this->addOption('local_logout_path', '/logout');
        $this->addOption('target_path_parameter', $this->defaultSuccessHandlerOptions['target_path_parameter']);
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);
        $node->children()
            ->scalarNode('relying_party')->defaultValue(null)->end()
            ->scalarNode('login_path')->defaultValue('/saml/sp/login')->cannotBeEmpty()->end()
            ->scalarNode('check_path')->defaultValue('/saml/sp/acs')->cannotBeEmpty()->end()
            ->scalarNode('logout_path')->defaultValue('/saml/sp/logout')->cannotBeEmpty()->end()
            ->scalarNode('failure_path')->defaultValue('/saml/sp/failure')->cannotBeEmpty()->end()
            ->scalarNode('metadata_path')->defaultValue('/saml/sp/FederationMetadata.xml')->cannotBeEmpty()->end()
            ->scalarNode('discovery_path')->defaultValue('/saml/sp/discovery')->cannotBeEmpty()->end()
            ->scalarNode('local_logout_path')->defaultValue('/logout')->cannotBeEmpty()->end()
            ->booleanNode('create_user_if_not_exists')->defaultFalse()->end()
            ->arrayNode('services')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->arrayNode('idp')->isRequired()
                            ->children()
                                ->scalarNode('file')->end()
                                ->scalarNode('entity_id')->end()
                                ->scalarNode('id')->end()
                            ->end()
                        ->end()
                        ->arrayNode('sp')->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('config')->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity_id')->cannotBeEmpty()->isRequired()->end()
                                        ->scalarNode('base_url')->defaultValue(null)->end()
                                        ->booleanNode('want_assertions_signed')->cannotBeEmpty()->defaultFalse()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('signing')->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('id')->cannotBeEmpty()->end()
                                        ->scalarNode('cert_file')->cannotBeEmpty()->end()
                                        ->scalarNode('key_file')->cannotBeEmpty()->end()
                                        ->scalarNode('key_pass')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('meta')->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('id')->end()
                                        ->scalarNode('name_id_format')
                                            ->cannotBeEmpty()
                                            ->defaultValue('persistent')
                                        ->end()
                                        ->arrayNode('binding')->addDefaultsIfNotSet()
                                            ->children()
                                                ->enumNode('authn_request')
                                                    ->values(array('redirect', 'post'))
                                                    ->defaultValue('redirect')
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->enumNode('response')
                                                    ->values(array('redirect', 'post'))
                                                    ->defaultValue('post')
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
                ->end()
            ->end()
        ->end();
    }


    protected function createListener($container, $id, $config, $userProvider)
    {
        $this->addOption('login_path', $config['login_path']);
        $this->addOption('check_path', $config['check_path']);
        $this->addOption('logout_path', $config['logout_path']);
        $this->addOption('failure_path', $config['failure_path']);
        $this->addOption('metadata_path', $config['metadata_path']);
        $this->addOption('discovery_path', $config['discovery_path']);
        $this->addOption('local_logout_path', $config['local_logout_path']);

        $this->createServiceInfoCollection($container, $id, $config);
        $this->createStateStores($container, $id, $config);
        $this->createRelyingParties($container, $id, $config);

        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        if ($config['relying_party']) {
            $container
                    ->getDefinition($listenerId)
                    ->addMethodCall('setRelyingParty', array(new Reference($config['relying_party'])))
                ;
        } else {
            $container
                    ->getDefinition($listenerId)
                    ->addMethodCall('setRelyingParty', array(new Reference('aerial_ship_saml_sp.relying_party.composite.'.$id)))
                ;
        }

        return $listenerId;
    }




    protected function createServiceInfoCollection(ContainerBuilder $container, $id, array $config)
    {
        $collection = new DefinitionDecorator('aerial_ship_saml_sp.service_info_collection');
        $container->setDefinition("aerial_ship_saml_sp.service_info_collection.{$id}", $collection);

        foreach ($config['services'] as $name => $asConfig) {
            $this->createSPSigningProvider($container, $id, $name, $asConfig['sp']['signing']);
            $this->createSPEntityDescriptorBuilder($container, $id, $name, $asConfig['sp']['config'], $config['check_path'], $config['logout_path']);
            $this->createIDPEntityDescriptorBuilder($container, $id, $name, $asConfig['idp']);
            $this->createSPMetaProvider($container, $id, $name, $asConfig['sp']['meta']);

            $provider = new DefinitionDecorator('aerial_ship_saml_sp.service_info');

            $provider->replaceArgument(0, $id);
            $provider->replaceArgument(1, $name);
            $provider->replaceArgument(2, new Reference("aerial_ship_saml_sp.sp_entity_descriptor_builder.{$id}.{$name}"));
            $provider->replaceArgument(3, new Reference("aerial_ship_saml_sp.idp_entity_descriptor_provider.{$id}.{$name}"));
            $provider->replaceArgument(4, new Reference("aerial_ship_saml_sp.sp_meta_provider.{$id}.{$name}"));
            $provider->replaceArgument(5, new Reference("aerial_ship_saml_sp.sp_signing.{$id}.{$name}"));

            $container->setDefinition("aerial_ship_saml_sp.service_info.{$id}.{$name}", $provider);

            $collection->addMethodCall('add', array(new Reference("aerial_ship_saml_sp.service_info.{$id}.{$name}")));
        }
    }

    protected function createSPSigningProvider(ContainerBuilder $container, $id, $name, array $config)
    {
        $serviceID = "aerial_ship_saml_sp.sp_signing.{$id}.{$name}";
        if (isset($config['id'])) {
            $container->setAlias($serviceID, $config['id']);
        } else if (isset($config['cert_file']) &&
                isset($config['key_file'])
        ) {
            $service = new DefinitionDecorator('aerial_ship_saml_sp.sp_signing.file');
            $service->replaceArgument(1, $config['cert_file']);
            $service->replaceArgument(2, $config['key_file']);
            $service->replaceArgument(3, array_key_exists('key_pass', $config) ? $config['key_pass'] : null);
            $container->setDefinition($serviceID, $service);
        } else {
            $service = new DefinitionDecorator('aerial_ship_saml_sp.sp_signing.null');
            $container->setDefinition($serviceID, $service);
        }
    }

    protected function createSPEntityDescriptorBuilder(ContainerBuilder $container, $id, $name, array $config, $checkPath, $logoutPath)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.sp_entity_descriptor_builder');
        $service->replaceArgument(0, $name);
        $service->replaceArgument(1, new Reference("aerial_ship_saml_sp.sp_signing.{$id}.{$name}"));
        $service->replaceArgument(2, $config);
        $service->replaceArgument(3, $checkPath);
        $service->replaceArgument(4, $logoutPath);
        $container->setDefinition("aerial_ship_saml_sp.sp_entity_descriptor_builder.{$id}.{$name}", $service);
    }

    protected function createIDPEntityDescriptorBuilder(ContainerBuilder $container, $id, $name, array $config)
    {
        $serviceID = "aerial_ship_saml_sp.idp_entity_descriptor_provider.{$id}.{$name}";
        if (isset($config['id'])) {
            $container->setAlias($serviceID, $config['id']);
        } else {
            $service = new DefinitionDecorator('aerial_ship_saml_sp.idp_entity_descriptor_provider');
            $container->setDefinition($serviceID, $service);
            if (isset($config['file'])) {
                $service->addMethodCall('setFilename', array($config['file']));
                if (isset($config['entity_id'])) {
                    $service->addMethodCall('setEntityId', array($config['entity_id']));
                }
            }
        }
    }

    protected function createSPMetaProvider(ContainerBuilder $container, $id, $name, array $config)
    {
        $serviceID = "aerial_ship_saml_sp.sp_meta_provider.{$id}.{$name}";
        if (isset($config['id'])) {
            $container->setAlias($serviceID, $config['id']);
        } else {
            $service = new DefinitionDecorator('aerial_ship_saml_sp.sp_meta_provider');
            $service->replaceArgument(0, $config);
            $container->setDefinition("aerial_ship_saml_sp.sp_meta_provider.{$id}.{$name}", $service);
        }

    }


    protected function createStateStores(ContainerBuilder $container, $id, array $config)
    {
        $this->createRequestStore($container, $id, $config);
    }

    protected function createRequestStore(ContainerBuilder $container, $id, array $config)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.state.store.request');
        $service->replaceArgument(1, $id);
        $container->setDefinition('aerial_ship_saml_sp.state.store.request.'.$id, $service);
    }

    protected function createRelyingParties(ContainerBuilder $container, $id, array $config)
    {
        $this->createRelyingPartyDiscovery($container, $id, $config);
        $this->createRelyingPartyFederationMetadata($container, $id, $config);
        $this->createRelyingPartyAuthenticate($container, $id);
        $this->createRelyingPartyAssertionConsumer($container, $id);
        $this->createRelyingPartyLogout($container, $id);
        $this->createRelyingPartySSOSessionCheck($container, $id);
        $this->createRelyingPartyComposite($container, $id);
    }

    protected function createRelyingPartyDiscovery(ContainerBuilder $container, $id, array $config)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.discovery');
        $service->replaceArgument(1, new Reference("aerial_ship_saml_sp.service_info_collection.{$id}"));
        $container->setDefinition("aerial_ship_saml_sp.relying_party.discovery.{$id}", $service);
    }

    protected function createRelyingPartyFederationMetadata(ContainerBuilder $container, $id, array $config)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.federation_metadata');
        $service->replaceArgument(0, new Reference('aerial_ship_saml_sp.service_info_collection.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.federation_metadata.'.$id, $service);
    }

    protected function createRelyingPartyAuthenticate(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.authenticate');
        $service->replaceArgument(0, new Reference('aerial_ship_saml_sp.service_info_collection.'.$id));
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.state.store.request.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.authenticate.'.$id, $service);
    }

    protected function createRelyingPartyAssertionConsumer(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.assertion_consumer');
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.service_info_collection.'.$id));
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.state.store.request.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.assertion_consumer.'.$id, $service);
    }

    protected function createRelyingPartySSOSessionCheck(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.sso_session_check');
        $service->replaceArgument(0, $id);
        $container->setDefinition('aerial_ship_saml_sp.relying_party.sso_session_check.'.$id, $service);
    }

    protected function createRelyingPartyLogout(ContainerBuilder $container, $id)
    {
        $this->createRelyingPartyLogoutSendRequest($container, $id);
        $this->createRelyingPartyLogoutReceiveResponse($container, $id);
        $this->createRelyingPartyLogoutReceiveRequest($container, $id);

        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.logout');
        $container->setDefinition('aerial_ship_saml_sp.relying_party.logout.'.$id, $service);

        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.logout.receive_response.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.logout.receive_request.'.$id)));
        // must come after receive response
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.logout.send_request.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.logout.fallback')));
    }

    protected function createRelyingPartyLogoutSendRequest(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.logout.send_request');
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.service_info_collection.'.$id));
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.state.store.request.'.$id));
        $container->setDefinition("aerial_ship_saml_sp.relying_party.logout.send_request.{$id}", $service);
    }

    protected function createRelyingPartyLogoutReceiveResponse(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.logout.receive_response');
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.state.store.request.'.$id));
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.service_info_collection.'.$id));
        $container->setDefinition("aerial_ship_saml_sp.relying_party.logout.receive_response.{$id}", $service);
    }

    protected function createRelyingPartyLogoutReceiveRequest(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.logout.receive_request');
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.service_info_collection.'.$id));
        $container->setDefinition("aerial_ship_saml_sp.relying_party.logout.receive_request.{$id}", $service);
    }

    protected function createRelyingPartyComposite(ContainerBuilder $container, $id)
    {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.composite');
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.discovery.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.federation_metadata.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.authenticate.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.assertion_consumer.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.logout.'.$id)));
        // sso session check must be the last one since it can handle every request
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.sso_session_check.'.$id)));
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
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'security.authentication.provider.aerial_ship_saml_sp.'.$id;
        $provider = $container
                ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.aerial_ship_saml_sp'))
                ->replaceArgument(0, $id);

        if (isset($config['provider'])) {
            $adapter = new DefinitionDecorator('aerial_ship_saml_sp.user_provider_adapter');
            $adapter->replaceArgument(0, new Reference($userProviderId));
            $adapterID = 'aerial_ship_saml_sp.user_provider_adapter.'.$id;
            $container->setDefinition($adapterID, $adapter);

            $provider
                    ->replaceArgument(1, new Reference($adapterID))
                    ->replaceArgument(2, new Reference('security.user_checker'))
            ;
        }
        if (!isset($config['create_user_if_not_exists'])) {
            $config['create_user_if_not_exists'] = false;
        }
        $provider->replaceArgument(3, $config['create_user_if_not_exists']);

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
    protected function getListenerId()
    {
        return 'security.authentication.listener.aerial_ship_saml_sp';
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'aerial_ship_saml_sp';
    }


    /**
     * {@inheritDoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'security.authentication.form_entry_point.'.$id;

        $container
                ->setDefinition($entryPointId, new DefinitionDecorator('security.authentication.form_entry_point'))
                ->addArgument(new Reference('security.http_utils'))
                ->addArgument($config['login_path'])
                ->addArgument($config['use_forward'])
        ;

        return $entryPointId;
    }
}
