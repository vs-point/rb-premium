<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use Brick\Money\Money;
use VsPoint\RBPremium\DTO\Account\Account;
use VsPoint\RBPremium\DTO\Account\Balance;
use VsPoint\RBPremium\DTO\Account\CurrencyFolder;
use VsPoint\RBPremium\DTO\Account\AccountQuery;
use VsPoint\RBPremium\Enum\BalanceType;

final class AccountTest extends AbstractIntegrationTest
{
    public function testListReturnsAccounts(): void
    {
        $result = $this->client->accounts->list();

        self::assertNotEmpty($result->accounts, 'Sandbox should return at least one account.');
        self::assertContainsOnlyInstancesOf(Account::class, $result->accounts);
    }

    public function testListAccountHasExpectedFields(): void
    {
        $result = $this->client->accounts->list();
        $account = $result->accounts[0];

        self::assertNotNull($account->accountNumber);
        self::assertNotNull($account->iban);
        self::assertMatchesRegularExpression('/^[A-Z]{2}\d{2}/', $account->iban, 'IBAN should start with country code.');
    }

    public function testListPagination(): void
    {
        $page1 = $this->client->accounts->list(new AccountQuery(page: 1, size: 1));

        self::assertNotNull($page1->totalSize);
        self::assertNotNull($page1->totalPages);
        self::assertNotEmpty($page1->accounts);
    }

    public function testBalanceReturnsCurrencyFolders(): void
    {
        $accounts = $this->client->accounts->list();
        $accountNumber = $accounts->accounts[0]->accountNumber;
        self::assertNotNull($accountNumber);

        $balance = $this->client->accounts->balance($accountNumber);

        self::assertNotEmpty($balance->currencyFolders, 'Balance should contain at least one currency folder.');
        self::assertContainsOnlyInstancesOf(CurrencyFolder::class, $balance->currencyFolders);
    }

    public function testBalanceFolderHasBalances(): void
    {
        $accounts = $this->client->accounts->list();
        $accountNumber = $accounts->accounts[0]->accountNumber;
        self::assertNotNull($accountNumber);

        $balance = $this->client->accounts->balance($accountNumber);
        $folder = $balance->currencyFolders[0];

        self::assertNotNull($folder->currency);
        self::assertNotEmpty($folder->balances);
        self::assertContainsOnlyInstancesOf(Balance::class, $folder->balances);
    }

    public function testBalanceTypesAreEnums(): void
    {
        $accounts = $this->client->accounts->list();
        $accountNumber = $accounts->accounts[0]->accountNumber;
        self::assertNotNull($accountNumber);

        $balance = $this->client->accounts->balance($accountNumber);

        foreach ($balance->currencyFolders as $folder) {
            foreach ($folder->balances as $item) {
                self::assertInstanceOf(BalanceType::class, $item->balanceType);
            }
        }
    }

    public function testBalanceAmountCanProduceMoney(): void
    {
        $accounts = $this->client->accounts->list();
        $accountNumber = $accounts->accounts[0]->accountNumber;
        self::assertNotNull($accountNumber);

        $balance = $this->client->accounts->balance($accountNumber);

        foreach ($balance->currencyFolders as $folder) {
            foreach ($folder->balances as $item) {
                self::assertNotNull($item->value);
                self::assertInstanceOf(Money::class, $item->toMoney());
            }
        }
    }
}
