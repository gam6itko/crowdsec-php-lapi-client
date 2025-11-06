<?php

declare(strict_types=1);

namespace CrowdSec\LapiClient\Tests\Integration;

use CrowdSec\LapiClient\AlertsClient;
use CrowdSec\LapiClient\Constants;
use CrowdSec\LapiClient\Payload\Alert;
use CrowdSec\LapiClient\Storage\TokenStorage;
use CrowdSec\LapiClient\Tests\Constants as TestConstants;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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

    /**
     * @var AlertsClient
     */
    protected $alertsClient;

    private function addTlsConfig(&$bouncerConfigs, $tlsPath)
    {
        $bouncerConfigs['tls_cert_path'] = $tlsPath . '/bouncer.pem';
        $bouncerConfigs['tls_key_path'] = $tlsPath . '/bouncer-key.pem';
        $bouncerConfigs['tls_ca_cert_path'] = $tlsPath . '/ca-chain.pem';
        $bouncerConfigs['tls_verify_peer'] = true;
    }

    protected function setUp(): void
    {
        $this->useTls = (string)getenv('BOUNCER_TLS_PATH');

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

        $tokenStorage = new TokenStorage($this->watcherClient->getWatcher(), new ArrayAdapter());
        $this->alertsClient = new AlertsClient($this->configs, $tokenStorage);
    }

    /**
     * @covers ::push
     */
    public function testPush(): array
    {
        $now = new \DateTimeImmutable();
        $alertFull = new Alert(
            [
                'scenario' => 'crowdsec-lapi-test/integration',
                'scenario_hash' => 'abc123',
                'scenario_version' => '1.0',
                'message' => 'Message1',
                'events_count' => 3,
                'start_at' => $now->format('Y-m-d H:i:s'),
                'stop_at' => $now
                    ->add(new \DateInterval('PT4H'))
                    ->format('Y-m-d H:i:s'),
                'capacity' => 10,
                'leakspeed' => '10/1s',
                'simulated' => false,
                'remediation' => true,
            ],
            // source
            [
                'scope' => 'ip',
                'value' => '1.2.3.4',
                'ip' => '1.1.1.1',
                'range' => '1.2.3.4/32',
                'as_number' => 'AS12345',
                'as_name' => 'EXAMPLE-AS',
                'cn' => 'US',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            // events
            [
                [
                    'meta' => [
                        ['key' => 'path', 'value' => '/admin'],
                    ],
                    'timestamp' => $now->format('Y-m-d H:i:s'),
                ],
            ],
            // decisions
            [
                [
                    'origin' => 'lapi',
                    'type' => 'ban',
                    'scope' => 'ip',
                    'value' => '1.2.3.4',
                    'duration' => '4h',
                    'until' => $now
                        ->add(new \DateInterval('PT4H'))
                        ->format('Y-m-d H:i:s'),
                    'scenario' => 'crowdsec-lapi-test/integration',
                ],
            ],
            [
                ['key' => 'service', 'value' => 'phpunit'],
            ],
            ['http', 'probing']
        );
        $alertLite = new Alert(
            [
                'scenario' => 'crowdsec-lapi-test/integration',
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
            ],
            // source
            [
                'scope' => 'ip',
                'value' => '1.2.3.4',
                'ip' => '2.2.2.2',
                'range' => '1.2.3.4/32',
                'as_number' => 'AS12345',
                'as_name' => 'EXAMPLE-AS',
                'cn' => 'US',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            // events
            [
                [
                    'meta' => [
                        ['key' => 'path', 'value' => '/admin'],
                    ],
                    'timestamp' => $now->format('Y-m-d H:i:s'),
                ],
            ]
        );
        $result = $this->alertsClient->push([
            $alertFull,
            $alertLite
        ]);
        self::assertIsArray($result);
        self::assertCount(2, $result);
        return $result;
    }

    /**
     * @covers ::search
     * @depends testPush
     * @dataProvider searchProvider
     */
    public function testSearch(array $query, int $expectedCount): void
    {
        $result = $this->alertsClient->search($query);
        self::assertCount($expectedCount, $result);
    }

    public static function searchProvider(): iterable
    {
        yield 'empty' => [
            [],
            2
        ];

        yield 'ip - no' => [
            ['ip' => '19.17.11.7'],
            0
        ];

        yield 'ip - 1' => [
            ['ip' => '1.2.3.4'],
            1
        ];

        yield 'scenario' => [
            ['scenario' => 'crowdsec-lapi-test/integration'],
            2
        ];

        yield 'scope - ip' => [
            ['scope' => 'ip'],
            2,
        ];

        yield 'scope - ip:1.2.3.4' => [
            ['scope' => 'ip', 'value' => '1.2.3.4'],
            2,
        ];
        yield 'has_active_decision' => [
            ['has_active_decision' => true],
            1,
        ];
    }
}
