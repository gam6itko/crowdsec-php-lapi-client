<?php

namespace CrowdSec\LapiClient\Configuration\Alert;

use CrowdSec\Common\Configuration\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Labels extends AbstractConfiguration
{
    /**
     * Standalone builder for: labels: list<string>
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('labels');
        $root = $treeBuilder->getRootNode();

        $root  ->scalarPrototype()->cannotBeEmpty()->end();

        return $treeBuilder;
    }

    /**
     * Compositional helper: add labels under the given node.
     */
    public function add(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
            ->arrayNode('labels')->isRequired()
            ->scalarPrototype()->cannotBeEmpty()->end()
            ->end()
            ->end();
    }
}