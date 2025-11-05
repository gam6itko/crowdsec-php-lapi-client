<?php

declare(strict_types=1);

namespace CrowdSec\LapiClient\Tests\Unit;

/**
 * Test for Curl request handler.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2022+ CrowdSec
 * @license   MIT License
 */

use CrowdSec\LapiClient\BouncerClient;
use CrowdSec\LapiClient\Tests\MockedData;
use CrowdSec\LapiClient\TimeoutException;

/**
 * @uses \CrowdSec\LapiClient\Configuration::getConfigTreeBuilder
 * @uses \CrowdSec\LapiClient\BouncerClient::__construct
 * @uses \CrowdSec\LapiClient\BouncerClient::configure
 * @uses \CrowdSec\LapiClient\BouncerClient::formatUserAgent
 * @uses \CrowdSec\LapiClient\Configuration::addConnectionNodes
 * @uses \CrowdSec\LapiClient\Configuration::validate
 * @uses \CrowdSec\LapiClient\Configuration::addAppSecNodes
 * @uses \CrowdSec\LapiClient\BouncerClient::cleanHeadersForLog
 * @uses \CrowdSec\LapiClient\BouncerClient::cleanRawBodyForLog()
 *
 * @covers \CrowdSec\LapiClient\BouncerClient::getStreamDecisions
 * @covers \CrowdSec\LapiClient\BouncerClient::getFilteredDecisions
 * @covers \CrowdSec\LapiClient\BouncerClient::manageRequest
 * @covers \CrowdSec\LapiClient\BouncerClient::getAppSecDecision
 * @covers \CrowdSec\LapiClient\BouncerClient::manageAppSecRequest
 */
final class CurlTest extends AbstractClient
{
    public function testDecisionsStream()
    {
        // Success test
        $mockCurlRequest = $this->getCurlMock(['exec', 'getResponseHttpCode']);
        $mockCurlRequest->method('exec')->willReturn(
            MockedData::DECISIONS_STREAM_LIST
        );
        $mockCurlRequest->method('getResponseHttpCode')->willReturn(
            MockedData::HTTP_200
        );
        $client = new BouncerClient($this->configs, $mockCurlRequest);
        $decisionsResponse = $client->getStreamDecisions(true);

        $this->assertEquals(
            json_decode(MockedData::DECISIONS_STREAM_LIST, true),
            $decisionsResponse,
            'Success get decisions stream'
        );
    }

    public function testFilteredDecisions()
    {
        // Success test
        $mockCurlRequest = $this->getCurlMock(['exec', 'getResponseHttpCode']);
        $mockCurlRequest->method('exec')->willReturn(
            MockedData::DECISIONS_FILTER
        );
        $mockCurlRequest->method('getResponseHttpCode')->willReturn(
            MockedData::HTTP_200
        );
        $client = new BouncerClient($this->configs, $mockCurlRequest);
        $decisionsResponse = $client->getFilteredDecisions();

        $this->assertEquals(
            json_decode(MockedData::DECISIONS_FILTER, true),
            $decisionsResponse,
            'Success get filtered decisions'
        );
    }

    public function testAppSecDecision()
    {
        // Success test
        $mockCurlRequest = $this->getCurlMock(['exec', 'getResponseHttpCode']);
        $mockCurlRequest->method('exec')->willReturn(
            MockedData::APPSEC_ALLOWED
        );
        $mockCurlRequest->method('getResponseHttpCode')->willReturn(
            MockedData::HTTP_200
        );
        $client = new BouncerClient($this->configs, $mockCurlRequest);
        $headers = [
            'X-Crowdsec-Appsec-Ip' => 'test-value',
            'X-Crowdsec-Appsec-Host' => 'test-value',
            'X-Crowdsec-Appsec-User-Agent' => 'test-value',
            'X-Crowdsec-Appsec-Verb' => 'test-value',
            'X-Crowdsec-Appsec-Uri' => 'test-value',
            'X-Crowdsec-Appsec-Api-Key' => 'test-value',
        ];
        $appSecResponse = $client->getAppSecDecision($headers);

        $this->assertEquals(
            json_decode(MockedData::APPSEC_ALLOWED, true),
            $appSecResponse,
            'Success get appsec decision'
        );
    }

    public function testAppSecDecisionWithTimeout()
    {
        // Success test
        $mockCurlRequest = $this->getCurlMock(['exec', 'error', 'errno']);
        $mockCurlRequest->method('exec')->willReturn(false);
        $mockCurlRequest->method('errno')->willReturn(\CURLE_OPERATION_TIMEOUTED);
        $mockCurlRequest->method('error')->willReturn('Operation timed out');

        $client = new BouncerClient($this->configs, $mockCurlRequest);
        $headers = [
            'X-Crowdsec-Appsec-Ip' => 'test-value',
            'X-Crowdsec-Appsec-Host' => 'test-value',
            'X-Crowdsec-Appsec-User-Agent' => 'test-value',
            'X-Crowdsec-Appsec-Verb' => 'test-value',
            'X-Crowdsec-Appsec-Uri' => 'test-value',
            'X-Crowdsec-Appsec-Api-Key' => 'test-value',
        ];

        $error = false;
        $message = '';
        try {
            $client->getAppSecDecision($headers);
        } catch (TimeoutException $e) {
            $error = true;
            $message = $e->getMessage();
        }

        $this->assertEquals(
            true,
            $error,
            'A timeout should be thrown'
        );

        $this->assertEquals(
            'CURL call timeout: Operation timed out',
            $message,
            'A timeout should be thrown'
        );
    }

    public function testFilteredDecisionsWithTimeout()
    {
        // Success test
        $mockCurlRequest = $this->getCurlMock(['exec', 'error', 'errno']);
        $mockCurlRequest->method('exec')->willReturn(false);
        $mockCurlRequest->method('errno')->willReturn(\CURLE_OPERATION_TIMEOUTED);
        $mockCurlRequest->method('error')->willReturn('Operation timed out');

        $client = new BouncerClient($this->configs, $mockCurlRequest);

        $error = false;
        $message = '';
        try {
            $client->getFilteredDecisions();
        } catch (TimeoutException $e) {
            $error = true;
            $message = $e->getMessage();
        }
        $this->assertEquals(
            true,
            $error,
            'A timeout should be thrown'
        );

        $this->assertEquals(
            'CURL call timeout: Operation timed out',
            $message,
            'A timeout should be thrown'
        );
    }
}
