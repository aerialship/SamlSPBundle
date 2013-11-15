<?php

namespace AerialShip\SamlSPBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        ->end();

    }


    protected function createListener($container, $id, $config, $userProvider) {
        $this->addOption('login_path', $config['login_path']);
        $this->addOption('check_path', $config['check_path']);
        $this->addOption('logout_path', $config['logout_path']);
        $listenerId = parent::createListener($container, $id, $config, $userProvider);
        if (isset($config['relying_party'])) {
            $container
                    ->getDefinition($listenerId)
                    ->addMethodCall('setRelyingParty', array(new Reference($config['relying_party'])))
            ;
        }
        return $listenerId;
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