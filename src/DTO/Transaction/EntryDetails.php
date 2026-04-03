<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class EntryDetails
{
    public function __construct(
        #[SerializedName('transactionDetails')]
        public ?TransactionDetails $transactionDetails = null,
    ) {
    }
}
