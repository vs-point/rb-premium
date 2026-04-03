<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

/** ISO 20022 camt.053 bank transaction code. */
final readonly class BankTransactionCode
{
    public function __construct(
        #[SerializedName('domain')]
        public ?BankTransactionCodeDomain $domain = null,
        #[SerializedName('proprietary')]
        public ?BankTransactionCodeProprietary $proprietary = null,
    ) {
    }
}
