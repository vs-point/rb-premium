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

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');
        self::assertIsBool($result->lastPage);
    }

    public function testTransactionHasExpectedFields(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');
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

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

        $tx = $result->transactions[0];
        self::assertNotNull($tx->amount);
        self::assertInstanceOf(Money::class, $tx->amount->toMoney());
    }

    public function testTransactionAmountCurrencyIsCurrencyObject(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

        $tx = $result->transactions[0];
        self::assertNotNull($tx->amount);
        self::assertInstanceOf(Currency::class, $tx->amount->currency);
    }

    public function testTransactionCreditDebitIndicationIsEnum(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

        foreach ($result->transactions as $tx) {
            self::assertInstanceOf(CreditDebitIndication::class, $tx->creditDebitIndication);
        }
    }

    public function testTransactionEntryDetailsIsObject(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

        foreach ($result->transactions as $tx) {
            self::assertInstanceOf(EntryDetails::class, $tx->entryDetails);
        }
    }

    public function testTransactionInstructedAmountCanProduceMoney(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

        foreach ($result->transactions as $tx) {
            $instructedAmount = $tx->entryDetails?->transactionDetails?->instructedAmount;

            if ($instructedAmount === null) {
                continue;
            }

            $money = $instructedAmount->toMoney();

            self::assertInstanceOf(Money::class, $money);
            self::assertEquals($instructedAmount->currency, $money->getCurrency());
            self::assertTrue(
                $money->getAmount()->isEqualTo($instructedAmount->value),
                'toMoney() amount should match instructedAmount value.',
            );

            return;
        }

        self::fail('No transactions with instructedAmount found in sandbox — expected sandbox to always contain instructed amount data.');
    }

    public function testTransactionCounterPartyFields(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

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

        self::fail('No transactions with counterparty data found in sandbox — expected sandbox to always contain counterparty data.');
    }

    public function testTransactionRemittanceSymbols(): void
    {
        $result = $this->client->transactions->list($this->accountNumber, $this->currency, $this->query());

        self::assertNotEmpty($result->transactions, 'Sandbox should always return at least one transaction.');

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

        self::fail('No transactions with remittance symbols found in sandbox — expected sandbox to always contain remittance data.');
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
