<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service\Api;

use VsPoint\RBPremium\DTO\Account\AccountListResponse;
use VsPoint\RBPremium\DTO\Account\AccountQuery;
use VsPoint\RBPremium\DTO\Account\BalanceResponse;

final class AccountsService extends AbstractRBPremiumService
{
    public function list(?AccountQuery $query = null): AccountListResponse
    {
        return $this->doGet('/accounts', AccountListResponse::class, $query?->toArray() ?? []);
    }

    public function balance(string $accountNumber): BalanceResponse
    {
        return $this->doGet(sprintf('/accounts/%s/balance', $accountNumber), BalanceResponse::class);
    }
}
