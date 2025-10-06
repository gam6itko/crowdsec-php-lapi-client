<?php

namespace CrowdSec\LapiClient\Configuration\Alert;

use CrowdSec\Common\Configuration\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @psalm-import-type TMeta from \CrowdSec\LapiClient\Configuration\Alert\Metas
 *
 * @psalm-type TEvent = array{
 *     meta: list<TMeta>,
 *     timestamp: string
 * }
 */
class Events extends AbstractConfiguration
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        // TODO: Implement getConfigTreeBuilder() method.
    }
}