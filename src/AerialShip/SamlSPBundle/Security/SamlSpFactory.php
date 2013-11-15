<?php

namespace AerialShip\SamlSPBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class SamlSpFactory extends AbstractFactory
{

    function __construct() {
        $this->defaultSuccessHandlerOptions['login_path'] = '/login_saml';
        $this->defaultFailureHandlerOptions['login_path'] = '/login_saml';

        // these are available in listener->options[]
        $this->addOption('login_path', '/login_saml');
        $this->addOption('check_path', '/login_check_saml');
        $this->addOption('logout_path', '/logout_saml');
        $this->addOption('target_path_parameter', $this->defaultSuccessHandlerOptions['target_path_parameter']);
    }

    public function addConfiguration(NodeDefinition $node) {
        parent::addConfiguration($node);
        $node->children()
            ->scalarNode('relying_party')->defaultValue('aerial_ship_saml_sp.relying_party.default')->cannotBeEmpty()->end()
            ->scalarNode('login_path')->defaultValue('/login_saml')->cannotBeEmpty()->end()
            ->scalarNode('check_path')->defaultValue('/login_check_saml')->cannotBeEmpty()->end()
            ->scalarNode('logout_path')->defaultValue('/logout_saml')->cannotBeEmpty()->end()
            ->scalarNode('provider')->defaultValue('aerial_ship_saml_sp.user_provider.default')->cannotBeEmpty()->end()
            ->arrayNode('entity_descriptor')->cannotBeEmpty()
                ->children()
                    ->arrayNode('sp')->cannotBeEmpty()
                        ->children()
                            ->scalarNode('file')->end()
                            ->scalarNode('id')->end()
                        ->end()
                    ->end()
                    ->arrayNode('idp')->cannotBeEmpty()
                        ->children()
                            ->scalarNode('file')->end()
                            ->scalarNode('id')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('sp_meta')
                ->children()
                    ->arrayNode('config')
                        ->children()
                            ->scalarNode('name_id_format')->end()
                            ->arrayNode('binding')
                                ->children()
                                    ->scalarNode('authn_request')->end()
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

        $this->createRelyingParties($container, $id, $config);

        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        $container
                ->getDefinition($listenerId)
                ->addMethodCall('setRelyingParty', array(new Reference('aerial_ship_saml_sp.relying_party.composite.'.$id)))
        ;

        return $listenerId;
    }


    protected function createRelyingParties(ContainerBuilder $container, $id, $config) {
        $this->createEntityDescriptorProviders($container, $id, $config);
        $this->createSpMetaProvider($container, $id, $config);
        $this->createRelyingPartyErrorRecovery($container, $id);
        $this->createRelyingPartyAuthenticate($container, $id);
        $this->createRelyingPartyAssertionConsumer($container, $id);
        $this->createRelyingPartyComposite($container, $id);
    }

    protected function createRelyingPartyComposite(ContainerBuilder $container, $id) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.composite');
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.authenticate.'.$id)));
        $service->addMethodCall('append', array(new Reference('aerial_ship_saml_sp.relying_party.assertion_consumer.'.$id)));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.composite.'.$id, $service);
    }

    protected function createRelyingPartyErrorRecovery(ContainerBuilder $container, $id) {
        throw new \Exception('Not implemented');
    }

    protected function createRelyingPartyAuthenticate(ContainerBuilder $container, $id) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.authenticate');
        $service->replaceArgument(0, new Reference('aerial_ship_saml_sp.entity_descriptor_provider.sp.'.$id));
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.entity_descriptor_provider.idp.'.$id));
        $service->replaceArgument(2, new Reference('aerial_ship_saml_sp.sp_meta_provider.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.authenticate.'.$id, $service);
    }

    protected function createRelyingPartyAssertionConsumer(ContainerBuilder $container, $id) {
        $service = new DefinitionDecorator('aerial_ship_saml_sp.relying_party.assertion_consumer');
        $service->replaceArgument(1, new Reference('aerial_ship_saml_sp.entity_descriptor_provider.idp.'.$id));
        $container->setDefinition('aerial_ship_saml_sp.relying_party.assertion_consumer.'.$id, $service);
    }

    protected function createSpMetaProvider(ContainerBuilder $container, $id, $config) {
        $serviceID = 'aerial_ship_saml_sp.sp_meta_provider.'.$id;
        if (isset($config['sp_meta']['id'])) {
            $container->setAlias($serviceID, $config['sp_meta']['id']);
        } else if (isset($config['sp_meta']['config'])) {
            $service = new DefinitionDecorator('aerial_ship_saml_sp.sp_meta_provider');
            $service->replaceArgument(0, $config['sp_meta']['config']);
            $container->setDefinition($serviceID, $service);
        } else {
            throw new \RuntimeException('aerial_ship_saml_sp.sp_meta has to have either id or config');
        }
    }

    protected function createEntityDescriptorProviders(ContainerBuilder $container, $id, $config) {
        $this->createEntityDescriptorSingleProvider('sp', $container, $id, $config);
        $this->createEntityDescriptorSingleProvider('idp', $container, $id, $config);
    }

    protected function createEntityDescriptorSingleProvider($type, ContainerBuilder $container, $id, $config) {
        $serviceID = "aerial_ship_saml_sp.entity_descriptor_provider.{$type}.{$id}";
        if (isset($config['entity_descriptor'][$type]['id'])) {
            $container->setAlias($serviceID, $config['entity_descriptor'][$type]['id']);
        } else {
            $service = new DefinitionDecorator('aerial_ship_saml_sp.entity_descriptor_provider.'.$type);
            $provider = $container->setDefinition($serviceID, $service);
            if (isset($config['entity_descriptor'][$type]['file'])) {
                $file = $config['entity_descriptor'][$type]['file'];
                $provider->addMethodCall('setFilename', array($file));
            }
        }
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