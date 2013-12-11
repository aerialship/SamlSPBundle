<?php

namespace AerialShip\SamlSPBundle\Tests\DependencyInjection\Security;

use AerialShip\SamlSPBundle\DependencyInjection\Security\Factory\SamlSpFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;


class SamlSpFactoryConfiguration implements ConfigurationInterface
{
    /** @var \AerialShip\SamlSPBundle\DependencyInjection\Security\Factory\SamlSpFactory  */
    private $factory;

    /** @var  string */
    private $name;



    public function __construct(SamlSpFactory $factory, $name)
    {
        $this->factory = $factory;
        $this->name = $name;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->name);
        $this->factory->addConfiguration($rootNode);
        return $treeBuilder;
    }


    /**
     * @param array $config
     * @return array
     */
    public function processConfiguration(array $config) {
        $processor = new Processor();
        $result = $processor->processConfiguration($this,
            array($this->name => $config)
        );
        return $result;
    }


    public function processCommonConfiguration() {
        return $this->processConfiguration($this->getCommonConfiguration());
    }

    public function getCommonConfiguration() {
        return array(
            'services' => array(
                'aaa' => array(
                    'idp' => array(
                        'file' => 'name.xml'
                    ),
                    'sp' => array(
                        'config' => array(
                            'entity_id' => 'entity.id'
                        )
                    )
                )
            )
        );

    }
} 