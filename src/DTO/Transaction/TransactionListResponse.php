<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TransactionListResponse
{
    /**
     * @param Transaction[] $transactions
     */
    public function __construct(
        #[SerializedName('transactions')]
        public array $transactions = [],
        #[SerializedName('lastPage')]
        public ?bool $lastPage = null,
    ) {
    }
}
