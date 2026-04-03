<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use PHPUnit\Framework\TestCase;
use VsPoint\RBPremium\Enum\Environment;
use VsPoint\RBPremium\RBPremiumClient;
use VsPoint\RBPremium\RBPremiumConfig;

abstract class AbstractIntegrationTest extends TestCase
{
    protected RBPremiumClient $client;

    protected function setUp(): void
    {
        $clientId = (string) getenv('RB_PREMIUM_CLIENT_ID');
        $certPath = (string) getenv('RB_PREMIUM_CERT_PATH');

        if ($clientId === '' || $certPath === '') {
            self::markTestSkipped('RB_PREMIUM_CLIENT_ID and RB_PREMIUM_CERT_PATH environment variables must be set.');
        }

        $config = new RBPremiumConfig(
            clientId: $clientId,
            certPath: $certPath,
            certPassword: getenv('RB_PREMIUM_CERT_PASSWORD') ?: null,
            keyPath: getenv('RB_PREMIUM_KEY_PATH') ?: null,
            keyPassword: getenv('RB_PREMIUM_KEY_PASSWORD') ?: null,
            environment: Environment::Sandbox,
        );

        $this->client = RBPremiumClient::create($config);
    }
}
