<?php

namespace CrowdSec\LapiClient\Payload;

use CrowdSec\Common\Configuration\AbstractConfiguration;
use CrowdSec\LapiClient\Configuration\Alert as AlertConf;
use CrowdSec\LapiClient\Configuration\Alert\Decision;
use CrowdSec\LapiClient\Configuration\Alert\Event;
use CrowdSec\LapiClient\Configuration\Alert\Meta;
use CrowdSec\LapiClient\Configuration\Alert\Source;
use Symfony\Component\Config\Definition\Processor;

/**
 * @psalm-import-type TAlert    from AlertConf
 * @psalm-import-type TEvent    from Event
 * @psalm-import-type TDecision from Decision
 * @psalm-import-type TSource   from Source
 * @psalm-import-type TMeta     from Meta
 *
 * @psalm-suppress InvalidPropertyAssignmentValue
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
    private $metaList = [];

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
        $this->configureMetaList($processor, $metaList);
        $this->labels = \array_filter($labels);
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
        if ([] !== $this->metaList) {
            $result['meta'] = $this->metaList;
        }
        if ([] !== $this->labels) {
            $result['labels'] = $this->labels;
        }
        return $result;
    }

    private function configureProperties(Processor $processor, array $properties): void
    {
        $configuration = new AlertConf();
        $this->properties = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($properties)]);
    }

    /**
     * @param ?TSource $source
     */
    private function configureSource(Processor $processor, ?array $source): void
    {
        if (null === $source) {
            return;
        }

        $configuration = new Source();
        $this->source = $processor->processConfiguration($configuration, [$configuration->cleanConfigs($source)]);
    }

    /**
     * @param list<TDecision> $list
     */
    private function configureDecisions(Processor $processor, array $list): void
    {
        $this->decisions = $this->handleList($processor, new Decision(), $list);
    }

    /**
     * @param list<TEvent> $list
     */
    private function configureEvents(Processor $processor, array $list): void
    {
        $this->events = $this->handleList($processor, new Event(), $list);
    }

    /**
     * @param list<TMeta> $list
     */
    private function configureMetaList(Processor $processor, array $list): void
    {
        $this->metaList = $this->handleList($processor, new Meta(), $list);
    }

    private function handleList(Processor $processor, AbstractConfiguration $param, array $list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $processor->processConfiguration($param, [$param->cleanConfigs($item)]);
        }
        return $result;
    }

}