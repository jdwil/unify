<?php

namespace JDWil\Unify\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('unify');

        $rootNode
            ->children()
                ->arrayNode('xdebug')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                        ->integerNode('port')->defaultValue(9000)->end()
                    ->end()
                ->end() // xdebug
                ->scalarNode('autoload_path')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
