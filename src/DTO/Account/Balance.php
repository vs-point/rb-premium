<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Account;

use Brick\Math\BigDecimal;
use Brick\Money\Currency;
use Brick\Money\Money;
use Symfony\Component\Serializer\Attribute\SerializedName;
use VsPoint\RBPremium\Enum\BalanceType;

final readonly class Balance
{
    public function __construct(
        #[SerializedName('balanceType')]
        public ?BalanceType $balanceType = null,
        #[SerializedName('value')]
        public ?BigDecimal $value = null,
        #[SerializedName('currency')]
        public ?Currency $currency = null,
    ) {
    }

    public function toMoney(): ?Money
    {
        if ($this->value === null || $this->currency === null) {
            return null;
        }

        return Money::of($this->value, $this->currency);
    }
}
