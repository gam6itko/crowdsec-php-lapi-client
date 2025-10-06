<?php

namespace CrowdSec\LapiClient\Payload;

use CrowdSec\LapiClient\Configuration\Alert\Decisions;
use CrowdSec\LapiClient\Configuration\Alert\Events;
use CrowdSec\LapiClient\Configuration\Alert\Metas;
use CrowdSec\LapiClient\Configuration\Alert\Source;
use Symfony\Component\Config\Definition\Processor;

/**
 * @psalm-import-type TAlert    from \CrowdSec\LapiClient\Configuration\Alert
 * @psalm-import-type TEvent    from \CrowdSec\LapiClient\Configuration\Events
 * @psalm-import-type TDecision from \CrowdSec\LapiClient\Configuration\Decisions
 * @psalm-import-type TSource   from \CrowdSec\LapiClient\Configuration\Source
 * @psalm-import-type TMeta     from \CrowdSec\LapiClient\Configuration\Metas
 */
class Alert
{
    /**
     * @var list<TAlert>
     */
    private $properties;

    /**
     * @var list<TEvent>
     */
    private $events = [];

    /**
     * @var list<TDecision>
     */
    private $decisions = [];

    /**
     * @var ?TSource
     */
    private $source = null;

    /**
     * @var list<TMeta>
     */
    private $metas = [];

    /**
     * @var list<string>
     */
    private $labels = [];

    /**
     * @param TAlert $properties
     * @param list<TDecision> $decisions
     * @param list<TEvent> $events
     * @param ?TSource $source
     * @param list<TMeta> $metaList
     * @param list<string> $labels
     */
    public function __construct(
        array  $properties,
        ?array $source = null,
        array  $decisions = [],
        array  $events = [],
        array  $metaList = [],
        array  $labels = []
    )
    {
        $processor = new Processor();
        $this->configureProperties($processor, $properties);
        $this->configureSource($processor, $source);
        $this->configureDecisions($processor, $decisions);
        $this->configureEvents($processor, $events);
        $this->configureMetas($processor, $metaList);
        $this->configureLabels($processor, $labels);
    }

    public function toArray(): array
    {
        $result = $this->properties;
        if ([] !== $this->decisions) {
            $result['decisions'] = $this->decisions;
        }
        if ([] !== $this->events) {
            $result['events'] = $this->events;
        }
        if (null !== $this->source) {
            $result['source'] = $this->source;
        }
        if ([] !== $this->metas) {
            $result['metas'] = $this->metas;
        }
        if ([] !== $this->labels) {
            $result['labels'] = $this->labels;
        }
        return $result;
    }

    private function configureProperties(Processor $processor, array $properties): void
    {
        $configuration = new \CrowdSec\LapiClient\Configuration\Alert();
        $this->properties = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($properties)]);
    }

    private function configureSource(Processor $processor, ?array $source): void
    {
        if (null === $source) {
            return;
        }

        $configuration = new Source();
        $this->source = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($source)]);
    }

    private function configureDecisions(Processor $processor, array $list): void
    {
        $configuration = new Decisions();
        $this->decisions = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($list)]);
    }

    private function configureEvents(Processor $processor, array $list): void
    {
        $configuration = new Events();
        $this->events = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($list)]);
    }

    private function configureMetas(Processor $processor, array $metas): void
    {
        $configuration = new Metas();
        $this->metas = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($metas)]);
    }

    private function configureLabels(Processor $processor, array $labels)
    {
        $configuration = new \CrowdSec\LapiClient\Configuration\Alert\Labels();
        $this->labels = $processor->processConfiguration($configuration, [$labels]);
    }
}