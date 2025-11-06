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
            Constants::ALERTS_PUSH,
            $alerts
        );
    }

    /**
     * @param TSearchQuery $query
     * @return array
     */
    public function search(array $query): array
    {
        $this->login();
        return $this->manageRequest(
            'GET',
            Constants::ALERTS_SEARCH,
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
