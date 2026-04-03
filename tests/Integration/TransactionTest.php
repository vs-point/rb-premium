<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;
use Brick\Money\Currency;
use Brick\Money\Money;
use VsPoint\RBPremium\DTO\Transaction\EntryDetails;
use VsPoint\RBPremium\DTO\Transaction\Transaction;
use VsPoint\RBPremium\DTO\Transaction\TransactionQuery;
use VsPoint\RBPremium\Enum\CreditDebitIndication;

final class TransactionTest extends AbstractIntegrationTest
{
    private string $accountNumber;
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $accounts = $this->client->accounts->list();

        if ($accounts->accounts === []) {
            self::markTestSkipped('No accounts available in sandbox.');
        }

        $account = $accounts->accounts[0];
        self::assertNotNull($account->accountNumber);

        $this->accountNumber = $account->accountNumber;

        if ($account->mainCurrency !== null) {
            $this->currency = $account->mainCurrency;
        } else {
            $balance = $this->client->accounts->balance($this->accountNumber);
            $currency = $balance->currencyFolders[0]?->currency ?? null;

            if ($currency === null) {
                self::markTestSkipped('Cannot determine account currency from sandbox.');
            }

            $this->currency = $currency;
        }
    }

    private function query(?int $page = null): TransactionQuery
    {
        return new TransactionQuery(
            from: LocalDate::now(TimeZone::utc())->minusMonths(3),
            to: LocalDate::now(TimeZone::utc()),
            page: $page,
        );
    }

    public function testListReturnsTransactions(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertIsArray($result->transactions);
        self::assertIsBool($result->lastPage);
    }

    public function testTransactionHasExpectedFields(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        if ($result->transactions === []) {
            self::markTestSkipped('No transactions in sandbox for the last 3 months.');
        }

        self::assertContainsOnlyInstancesOf(Transaction::class, $result->transactions);

        $tx = $result->transactions[0];
        self::assertNotNull($tx->entryReference);
        self::assertNotNull($tx->amount);
        self::assertNotNull($tx->bookingDate);
        self::assertNotNull($tx->valueDate);
    }

    public function testTransactionAmountCanProduceMoney(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        if ($result->transactions === []) {
            self::markTestSkipped('No transactions in sandbox for the last 3 months.');
        }

        $tx = $result->transactions[0];
        self::assertNotNull($tx->amount);
        self::assertInstanceOf(Money::class, $tx->amount->toMoney());
    }

    public function testTransactionAmountCurrencyIsCurrencyObject(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        if ($result->transactions === []) {
            self::markTestSkipped('No transactions in sandbox for the last 3 months.');
        }

        $tx = $result->transactions[0];
        self::assertNotNull($tx->amount);
        self::assertInstanceOf(Currency::class, $tx->amount->currency);
    }

    public function testTransactionCreditDebitIndicationIsEnum(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        if ($result->transactions === []) {
            self::markTestSkipped('No transactions in sandbox for the last 3 months.');
        }

        foreach ($result->transactions as $tx) {
            self::assertInstanceOf(CreditDebitIndication::class, $tx->creditDebitIndication);
        }
    }

    public function testTransactionEntryDetailsIsObject(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        if ($result->transactions === []) {
            self::markTestSkipped('No transactions in sandbox for the last 3 months.');
        }

        foreach ($result->transactions as $tx) {
            self::assertInstanceOf(EntryDetails::class, $tx->entryDetails);
        }
    }

    public function testTransactionCounterPartyFields(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        foreach ($result->transactions as $tx) {
            $counterParty = $tx->entryDetails?->transactionDetails?->relatedParties?->counterParty;

            if ($counterParty === null) {
                continue;
            }

            $hasAccount = $counterParty->account?->accountNumber !== null
                || $counterParty->account?->iban !== null;

            self::assertTrue(
                $hasAccount || $counterParty->name !== null,
                'Counter party should have at least a name or an account.',
            );

            return;
        }

        self::markTestSkipped('No transactions with counterparty data found in sandbox.');
    }

    public function testTransactionRemittanceSymbols(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        foreach ($result->transactions as $tx) {
            $ref = $tx->entryDetails?->transactionDetails?->remittanceInformation?->creditorReferenceInformation;

            if ($ref === null) {
                continue;
            }

            if ($ref->variable !== null) {
                self::assertMatchesRegularExpression('/^\d+$/', $ref->variable);
            }

            return;
        }

        self::markTestSkipped('No transactions with remittance symbols found in sandbox.');
    }

    public function testPagination(): void
    {
        $page1 = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query(page: 1));
        $page2 = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query(page: 2));

        if ($page1->lastPage === true) {
            self::markTestSkipped('Only one page of transactions available in sandbox.');
        }

        $ids1 = array_map(static fn (Transaction $tx) => $tx->entryReference, $page1->transactions);
        $ids2 = array_map(static fn (Transaction $tx) => $tx->entryReference, $page2->transactions);

        self::assertEmpty(array_intersect($ids1, $ids2), 'Pages should not contain duplicate transactions.');
    }
}
