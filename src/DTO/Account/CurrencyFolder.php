<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Account;

use Brick\Money\Currency;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class CurrencyFolder
{
    /**
     * @param Balance[] $balances
     */
    public function __construct(
        #[SerializedName('currency')]
        public ?Currency $currency = null,
        #[SerializedName('status')]
        public ?string $status = null,
        #[SerializedName('balances')]
        public array $balances = [],
    ) {
    }
}
