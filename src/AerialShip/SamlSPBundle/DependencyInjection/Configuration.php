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
        $rootNode = $treeBuilder->root('aerial_ship_saml_sp');

        $rootNode
            ->children()
                ->arrayNode('entity_descriptor')
                    ->children()
                        ->arrayNode('sp')
                            ->children()
                                ->scalarNode('file')->end()
                                ->scalarNode('id')->end()
                            ->end()
                        ->end()
                        ->arrayNode('idp')
                            ->children()
                                ->scalarNode('file')->end()
                                ->scalarNode('id')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

} 