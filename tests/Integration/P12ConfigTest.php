<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use PHPUnit\Framework\TestCase;
use VsPoint\RBPremium\DTO\Account\Account;
use VsPoint\RBPremium\Enum\Environment;
use VsPoint\RBPremium\RBPremiumClient;
use VsPoint\RBPremium\RBPremiumInlineConfig;

/**
 * Ověřuje načtení certifikátu přímo z .p12 souboru přes openssl_pkcs12_read().
 *
 * Vyžaduje env proměnné:
 *   RB_PREMIUM_CLIENT_ID   — X-IBM-Client-Id
 *   RB_PREMIUM_P12_PATH    — cesta k .p12 souboru
 *   RB_PREMIUM_P12_PASSWORD — heslo k .p12 (volitelné, výchozí prázdný řetězec)
 *
 * Poznámka: openssl_pkcs12_read() může selhat na serverech s OpenSSL ≥ 3.x,
 * pokud je .p12 zašifrován legacy algoritmy (RC2, 3DES). V takovém případě
 * proveď jednorázovou konverzi přes příkazový řádek.
 */
final class P12ConfigTest extends TestCase
{
    private RBPremiumClient $client;

    protected function setUp(): void
    {
        $clientId = (string) getenv('RB_PREMIUM_CLIENT_ID');
        $p12Path = (string) getenv('RB_PREMIUM_P12_PATH');

        if ($clientId === '' || $p12Path === '') {
            self::markTestSkipped('RB_PREMIUM_CLIENT_ID and RB_PREMIUM_P12_PATH environment variables must be set.');
        }

        $p12Content = file_get_contents($p12Path);

        if ($p12Content === false) {
            self::fail(sprintf('Cannot read .p12 file: %s', $p12Path));
        }

        $p12Password = getenv('RB_PREMIUM_P12_PASSWORD') ?: '';

        if (!openssl_pkcs12_read($p12Content, $certs, $p12Password)) {
            self::fail('Failed to parse .p12 certificate: ' . openssl_error_string());
        }

        $config = new RBPremiumInlineConfig(
            clientId: $clientId,
            certPem: $certs['cert'],
            keyPem: $certs['pkey'],
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
