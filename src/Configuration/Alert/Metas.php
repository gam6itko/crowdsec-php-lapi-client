<?php

namespace CrowdSec\LapiClient\Configuration\Alert;

use CrowdSec\Common\Configuration\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @psalm-type TMeta = array{
 *     key: string,
 *     value: string
 * }
 */
class Metas extends AbstractConfiguration
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        // TODO: Implement getConfigTreeBuilder() method.
    }
}