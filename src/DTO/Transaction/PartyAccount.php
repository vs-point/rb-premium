<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class PartyAccount
{
    public function __construct(
        #[SerializedName('iban')]
        public ?string $iban = null,
        #[SerializedName('accountNumberPrefix')]
        public ?string $accountNumberPrefix = null,
        #[SerializedName('accountNumber')]
        public ?string $accountNumber = null,
    ) {
    }
}
