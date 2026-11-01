<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Unit;

use PHPUnit\Framework\TestCase;
use VsPoint\RBPremium\DTO\Statement\StatementListResponse;
use VsPoint\RBPremium\DTO\Transaction\TransactionListResponse;
use VsPoint\RBPremium\RBPremiumClient;

/**
 * Tvar odpovědí produkčního RB Premium API (ověřeno 2026-11-01 na firemním účtu, data anonymizovaná).
 * Liší se od sandboxu i od OpenAPI specifikace — viz CLAUDE.md „Produkce vs. sandbox“.
 */
final class ProductionResponseDeserializationTest extends TestCase
{
    public function testCardPaymentCarriesTransactionCodeAndCardNumberFromTransactionDetails(): void
    {
        $response = $this->transactions([
            [
                'entryReference' => '9582035575',
                'amount' => ['value' => -2421.49, 'currency' => 'CZK'],
                'creditDebitIndication' => 'DBIT',
                'bookingDate' => '2026-10-03T23:59:59.000+02:00',
                'valueDate' => '2026-10-01T23:59:59.000+02:00',
                'bankTransactionCode' => ['code' => '30000201000'],
                'entryDetails' => [
                    'transactionDetails' => [
                        'references' => [],
                        'instructedAmount' => ['value' => 95.2, 'currency' => 'EUR', 'exchangeRate' => 25.436],
                        'relatedParties' => [],
                        'remittanceInformation' => [
                            'unstructured' => '95,20 EUR;GOOGLE*WORKSPACE; CC GOOGLE.COM; IRL',
                            'creditorReferenceInformation' => ['constant' => '1178'],
                        ],
                        'paymentCardNumber' => '547872XXXXXX0000',
                    ],
                ],
            ],
        ]);

        $transaction = $response->transactions[0];
        self::assertSame('30000201000', $transaction->bankTransactionCode?->code);
        self::assertNull($transaction->bankTransactionCode->domain);
        self::assertSame('547872XXXXXX0000', $transaction->entryDetails?->transactionDetails?->paymentCardNumber);
        self::assertSame('547872XXXXXX0000', $transaction->cardNumber());
        self::assertNull($transaction->paymentCardNumber, 'Produkce číslo karty na transakci neposílá.');
    }

    public function testTransferHasCodeAndNoCard(): void
    {
        $transaction = $this->transactions([
            [
                'entryReference' => '9574252911',
                'amount' => ['value' => -21820.0, 'currency' => 'CZK'],
                'creditDebitIndication' => 'DBIT',
                'bookingDate' => '2026-10-01T23:59:59.000+02:00',
                'bankTransactionCode' => ['code' => '10000101000'],
                'entryDetails' => ['transactionDetails' => ['remittanceInformation' => ['unstructured' => 'nájem']]],
            ],
        ])->transactions[0];

        self::assertSame('10000101000', $transaction->bankTransactionCode?->code);
        self::assertNull($transaction->cardNumber());
    }

    public function testSandboxCardNumberOnTransactionIsStillRead(): void
    {
        $transaction = $this->transactions([
            ['entryReference' => '1', 'paymentCardNumber' => '1234XXXXXXXX5678', 'entryDetails' => ['transactionDetails' => []]],
        ])->transactions[0];

        self::assertSame('1234XXXXXXXX5678', $transaction->cardNumber());
    }

    public function testStatementListReadsStatementFormats(): void
    {
        $serializer = RBPremiumClient::createSerializer();
        $response = $serializer->deserialize((string) json_encode([
            'statements' => [[
                'statementId' => 'aae3eeaa-29a2-4ecd-8b2c-ff54ffe6569c',
                'accountId' => 1,
                'accountNumber' => '1234567890',
                'statementNumber' => '10/2026',
                'dateFrom' => '2026-10-01',
                'dateTo' => '2026-10-31',
                'statementFormats' => ['PDF', 'XML'],
                'currency' => 'CZK',
            ]],
            'first' => true,
            'last' => true,
        ]), StatementListResponse::class, 'json');

        self::assertInstanceOf(StatementListResponse::class, $response);
        self::assertSame(['PDF', 'XML'], $response->statements[0]->formats);
        self::assertSame('10/2026', $response->statements[0]->statementNumber);
    }

    /**
     * @param list<array<string, mixed>> $transactions
     */
    private function transactions(array $transactions): TransactionListResponse
    {
        $response = RBPremiumClient::createSerializer()->deserialize(
            (string) json_encode(['lastPage' => true, 'transactions' => $transactions]),
            TransactionListResponse::class,
            'json',
        );
        self::assertInstanceOf(TransactionListResponse::class, $response);

        return $response;
    }
}
