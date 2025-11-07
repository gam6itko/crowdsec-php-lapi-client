<?php

declare(strict_types=1);

namespace CrowdSec\LapiClient;

use CrowdSec\Common\Client\RequestHandler\RequestHandlerInterface;
use CrowdSec\LapiClient\Configuration\Alert;
use CrowdSec\LapiClient\Storage\TokenStorageInterface;
use Psr\Log\LoggerInterface;

/**
 * @psalm-import-type TAlert from \CrowdSec\LapiClient\Configuration\Alert
 *
 * @psalm-type TSearchQuery = array{
 *     scope?: string,
 *     value?: string,
 *     scenario?: string,
 *     ip?: string,
 *     range?: string,
 *     since?: string,
 *     until?: string,
 *     simulated?: boolean,
 *     has_active_decision?: boolean,
 *     decision_type?: string,
 *     limit?: number,
 *     origin?: string
 * }
 *
 * @psalm-type TDeleteQuery = array{
 *     scope?: string,
 *     value?: string,
 *     scenario?: string,
 *     ip?: string,
 *     range?: string,
 *     since?: string,
 *     until?: string,
 *     has_active_decision?: boolean,
 *     alert_source?: string
 * }
 */
class AlertsClient extends AbstractLapiClient
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        array $configs,
        TokenStorageInterface $tokenStorage,
        ?RequestHandlerInterface $requestHandler = null,
        ?LoggerInterface $logger = null
    )
    {
        $this->tokenStorage = $tokenStorage;
        parent::__construct($configs, $requestHandler, $logger);
    }

    /**
     * @param list<TAlert|Alert> $alerts
     *
     * @return list<string>
     */
    public function push(array $alerts): array
    {
        $this->login();
        return $this->manageRequest(
            'POST',
            Constants::ALERTS,
            $alerts
        );
    }

    /**
     * Search for alerts.
     *
     *     scope - Show alerts for this scope.
     *     value - Show alerts for this value (used with scope).
     *     scenario - Show alerts for this scenario.
     *     ip - IP to search for (shorthand for scope=ip&value=).
     *     range - Range to search for (shorthand for scope=range&value=).
     *     since - Search alerts newer than delay (format must be compatible with time.ParseDuration).
     *     until - Search alerts older than delay (format must be compatible with time.ParseDuration).
     *     simulated - If set to true, decisions in simulation mode will be returned as well.
     *     has_active_decision: Only return alerts with decisions not expired yet.
     *     decision_type: Restrict results to alerts with decisions matching given type.
     *     limit: Number of alerts to return.
     *     origin: Restrict results to this origin (ie. lists,CAPI,cscli).
     *
     * @param TSearchQuery $query
     * @return array
     */
    public function search(array $query): array
    {
        $this->login();
        return $this->manageRequest(
            'GET',
            Constants::ALERTS,
            $query
        );
    }

    /**
     * Delete alerts by condition.
     *
     * @param TDeleteQuery $query
     */
    public function delete(array $query): array
    {
        $this->login();
        return $this->manageRequest(
            'DELETE',
            Constants::ALERTS,
            $query
        );
    }

    private function login(): void
    {
        $token = $this->tokenStorage->retrieveToken();
        if (null === $token) {
            throw new ClientException('Login fail');
        }
        $this->headers['Authorization'] = "Bearer $token";
    }
}
