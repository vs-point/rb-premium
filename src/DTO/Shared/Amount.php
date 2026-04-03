<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Shared;

use Brick\Math\BigDecimal;
use Brick\Money\Currency;
use Brick\Money\Money;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Amount
{
    public function __construct(
        #[SerializedName('value')]
        public BigDecimal $value,
        #[SerializedName('currency')]
        public Currency $currency,
    ) {
    }

    public function toMoney(): Money
    {
        return Money::of($this->value, $this->currency);
    }
}
