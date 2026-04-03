<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class BankTransactionCodeProprietary
{
    public function __construct(
        #[SerializedName('code')]
        public ?string $code = null,
        #[SerializedName('issuer')]
        public ?string $issuer = null,
    ) {
    }
}
