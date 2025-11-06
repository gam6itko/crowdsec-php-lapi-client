<?php

declare(strict_types=1);

namespace CrowdSec\LapiClient\Tests\Integration;

use CrowdSec\LapiClient\AlertsClient;
use CrowdSec\LapiClient\Constants;
use CrowdSec\LapiClient\Payload\Alert;
use CrowdSec\LapiClient\Tests\Constants as TestConstants;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CrowdSec\LapiClient\AlertsClient
 */
final class AlertsClientTest extends TestCase
{
    /**
     * @var array
     */
    protected $configs;
    /**
     * @var string
     */
    protected $useTls;
    /**
     * @var TestWatcherClient
     */
    protected $watcherClient;

    private function addTlsConfig(&$bouncerConfigs, $tlsPath)
    {
        $bouncerConfigs['tls_cert_path'] = $tlsPath . '/bouncer.pem';
        $bouncerConfigs['tls_key_path'] = $tlsPath . '/bouncer-key.pem';
        $bouncerConfigs['tls_ca_cert_path'] = $tlsPath . '/ca-chain.pem';
        $bouncerConfigs['tls_verify_peer'] = true;
    }

    protected function setUp(): void
    {
        $this->useTls = (string) getenv('BOUNCER_TLS_PATH');

        $bouncerConfigs = [
            'auth_type' => $this->useTls ? Constants::AUTH_TLS : Constants::AUTH_KEY,
            'api_key' => getenv('BOUNCER_KEY'),
            'api_url' => getenv('LAPI_URL'),
            'appsec_url' => getenv('APPSEC_URL'),
            'user_agent_suffix' => TestConstants::USER_AGENT_SUFFIX,
        ];
        if ($this->useTls) {
            $this->addTlsConfig($bouncerConfigs, $this->useTls);
        }

        $this->configs = $bouncerConfigs;
        $this->watcherClient = new TestWatcherClient($this->configs);
        // Delete all decisions
        $this->watcherClient->deleteAllDecisions();
        usleep(200000); // 200ms
    }

    /**
     * @covers ::pushAlerts
     */
    public function testAddAlert(): void
    {
        $alertMax = new Alert(
            [
                'scenario' => 'test/http-max',
                'scenario_hash' => 'abc123',
                'scenario_version' => '1.0',
                'message' => 'Message1',
                'events_count' => 3,
                'start_at' => '2025-01-01T00:00:00Z',
                'stop_at' => '2025-01-01T00:10:00Z',
                'capacity' => 10,
                'leakspeed' => '10/1s',
                'simulated' => false,
                'remediation' => true,
            ],
            [
                'scope' => 'ip',
                'value' => '1.2.3.4',
                'ip' => '1.2.3.4',
                'range' => '1.2.3.4/32',
                'as_number' => 'AS12345',
                'as_name' => 'EXAMPLE-AS',
                'cn' => 'US',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            [
                [
                    'origin' => 'lapi',
                    'type' => 'ban',
                    'scope' => 'ip',
                    'value' => '1.2.3.4',
                    'duration' => '4h',
                    'until' => '2025-01-01T04:00:00Z',
                    'scenario' => 'crowdsecurity/http-probing',
                ],
            ],
            [
                [
                    'meta' => [
                        ['key' => 'path', 'value' => '/admin'],
                    ],
                    'timestamp' => '2025-01-01T00:00:01Z',
                ],
            ],
            [
                ['key' => 'service', 'value' => 'phpunit'],
            ],
            ['http', 'probing']
        );
        $alertMin = new Alert([
            'scenario' => 'test/http-min',
            'scenario_hash' => 'xyz777',
            'scenario_version' => '1.0',
            'message' => 'Message2',
            'events_count' => 3,
            'start_at' => '2025-01-02T00:00:00Z',
            'stop_at' => '2025-01-02T00:10:00Z',
            'capacity' => 10,
            'leakspeed' => '10/1s',
            'simulated' => false,
            'remediation' => false,
        ]);
        $client = new AlertsClient($this->configs);
        $client->pushAlerts([
            $alertMax,
            $alertMin
        ]);

    }
}
