<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Brick\Math\BigDecimal;
use Brick\Money\Currency;
use Brick\Money\Money;
use Symfony\Component\Serializer\Attribute\SerializedName;

/** Original amount before currency conversion, including the applied exchange rate. */
final readonly class InstructedAmount
{
    public function __construct(
        #[SerializedName('value')]
        public BigDecimal $value,
        #[SerializedName('currency')]
        public Currency $currency,
        #[SerializedName('exchangeRate')]
        public ?BigDecimal $exchangeRate = null,
    ) {
    }

    public function toMoney(): Money
    {
        return Money::of($this->value, $this->currency);
    }
}
