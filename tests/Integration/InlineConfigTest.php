<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use PHPUnit\Framework\TestCase;
use VsPoint\RBPremium\DTO\Account\Account;
use VsPoint\RBPremium\Enum\Environment;
use VsPoint\RBPremium\RBPremiumClient;
use VsPoint\RBPremium\RBPremiumInlineConfig;

/**
 * Ověřuje, že RBPremiumInlineConfig funguje stejně jako RBPremiumConfig —
 * certifikát a klíč jsou předány jako PEM stringy místo cest k souborům.
 *
 * Používá stejné env proměnné jako ostatní integrační testy; certifikát
 * je načten ze souboru a předán jako string.
 */
final class InlineConfigTest extends TestCase
{
    private RBPremiumClient $client;

    protected function setUp(): void
    {
        $clientId = (string) getenv('RB_PREMIUM_CLIENT_ID');
        $certPath = (string) getenv('RB_PREMIUM_CERT_PATH');

        if ($clientId === '' || $certPath === '') {
            self::markTestSkipped('RB_PREMIUM_CLIENT_ID and RB_PREMIUM_CERT_PATH environment variables must be set.');
        }

        $certPem = file_get_contents($certPath);

        if ($certPem === false) {
            self::fail(sprintf('Cannot read cert file: %s', $certPath));
        }

        $keyPem = null;
        $keyPath = getenv('RB_PREMIUM_KEY_PATH') ?: null;

        if ($keyPath !== null) {
            $keyPem = file_get_contents($keyPath);

            if ($keyPem === false) {
                self::fail(sprintf('Cannot read key file: %s', $keyPath));
            }
        }

        $config = new RBPremiumInlineConfig(
            clientId: $clientId,
            certPem: $certPem,
            certPassword: getenv('RB_PREMIUM_CERT_PASSWORD') ?: null,
            keyPem: $keyPem,
            keyPassword: getenv('RB_PREMIUM_KEY_PASSWORD') ?: null,
            environment: Environment::Sandbox,
        );

        $this->client = RBPremiumClient::create($config);
    }

    public function testCanListAccounts(): void
    {
        $result = $this->client->accounts->list();

        self::assertNotEmpty($result->accounts, 'Sandbox should return at least one account.');
        self::assertContainsOnlyInstancesOf(Account::class, $result->accounts);
    }

    public function testCanGetBalance(): void
    {
        $accounts = $this->client->accounts->list();
        $accountNumber = $accounts->accounts[0]->accountNumber;
        self::assertNotNull($accountNumber);

        $balance = $this->client->accounts->balance($accountNumber);

        self::assertNotEmpty($balance->currencyFolders);
    }
}
