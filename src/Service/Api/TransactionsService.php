<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service\Api;

use Brick\Money\Currency;
use VsPoint\RBPremium\DTO\Transaction\TransactionListResponse;
use VsPoint\RBPremium\DTO\Transaction\TransactionQuery;

final class TransactionsService extends AbstractRBPremiumService
{
    public function list(string $accountNumber, Currency $currency, TransactionQuery $query): TransactionListResponse
    {
        return $this->doGet(
            sprintf('/accounts/%s/%s/transactions', $accountNumber, $currency->getCurrencyCode()),
            TransactionListResponse::class,
            $query->toArray(),
        );
    }
}
