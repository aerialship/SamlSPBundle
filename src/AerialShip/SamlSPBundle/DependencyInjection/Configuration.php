<?php

namespace AerialShip\SamlSPBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('aerial_ship_saml_sp');

        $root->children()
            ->enumNode('driver')
                ->values(array('orm', ''))
                ->cannotBeEmpty()
                ->defaultValue('orm')
                ->end()
            ->scalarNode('sso_state_entity_class')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
        ->end();

        return $treeBuilder;
    }

} 