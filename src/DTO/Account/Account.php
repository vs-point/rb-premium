<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Account;

use Brick\Money\Currency;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Account
{
    public function __construct(
        #[SerializedName('accountId')]
        public ?int $accountId = null,
        #[SerializedName('accountName')]
        public ?string $accountName = null,
        #[SerializedName('friendlyName')]
        public ?string $friendlyName = null,
        #[SerializedName('accountNumber')]
        public ?string $accountNumber = null,
        #[SerializedName('accountNumberPrefix')]
        public ?string $accountNumberPrefix = null,
        #[SerializedName('iban')]
        public ?string $iban = null,
        #[SerializedName('bankCode')]
        public ?string $bankCode = null,
        #[SerializedName('bankBicCode')]
        public ?string $bankBicCode = null,
        #[SerializedName('mainCurrency')]
        public ?Currency $mainCurrency = null,
        #[SerializedName('accountTypeId')]
        public ?string $accountTypeId = null,
    ) {
    }
}
