<?php

namespace CrowdSec\LapiClient\Configuration\Alert;

use CrowdSec\Common\Configuration\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @psalm-type TDecision = array{
 *     origin: string,
 *     type: string,
 *     scope: string,
 *     value: string,
 *     duration: string,
 *     until: string,
 *     scenario: string
 * }
 */
class Decisions extends AbstractConfiguration
{
    /** @var list<string> The list of each configuration tree key */
    protected $keys = [
        'origin',
        'type',
        'scope',
        'value',
        'duration',
        'until',
        'scenario',
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('decisions');
        $rootNode = $treeBuilder->getRootNode();

        //@formatter:off
        $rootNode
            ->arrayPrototype()
                ->children()
                    ->stringNode('origin')->isRequired()->cannotBeEmpty()->end()
                    ->stringNode('type')->isRequired()->cannotBeEmpty()->end()
                    ->stringNode('scope')->isRequired()->cannotBeEmpty()->end()
                    ->stringNode('value')->isRequired()->cannotBeEmpty()->end()
                    ->stringNode('duration')->isRequired()->cannotBeEmpty()->end()
                    ->stringNode('until')->isRequired()->cannotBeEmpty()->end()
                    ->stringNode('scenario')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;
        //@formatter:on

        return $treeBuilder;
    }
}