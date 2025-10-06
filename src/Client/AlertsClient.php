<?php

declare(strict_types=1);

namespace CrowdSec\LapiClient\Client;

use CrowdSec\LapiClient\Constants;

/**
 * @psalm-import-type TAlert from \CrowdSec\LapiClient\Configuration\Alert
 */
class AlertsClient extends AbstractLapiClient
{
    /**
     * @param list<TAlert> $alerts
     * @return list<string>
     */
    public function pushAlerts(array $alerts): array
    {
        return $this->manageRequest(
            'POST',
            Constants::ALERTS_PUSH,
            $alerts
        );
    }
}