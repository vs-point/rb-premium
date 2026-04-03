<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\FxRate;

use Brick\Math\BigDecimal;
use Brick\Money\Currency;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ExchangeRate
{
    public function __construct(
        #[SerializedName('currencyFrom')]
        public ?Currency $currencyFrom = null,
        #[SerializedName('currencyTo')]
        public ?Currency $currencyTo = null,
        #[SerializedName('exchangeRateBuy')]
        public ?BigDecimal $exchangeRateBuy = null,
        #[SerializedName('exchangeRateSell')]
        public ?BigDecimal $exchangeRateSell = null,
        #[SerializedName('exchangeRateCenter')]
        public ?BigDecimal $exchangeRateCenter = null,
        #[SerializedName('quotationType')]
        public ?string $quotationType = null,
        #[SerializedName('unitsFrom')]
        public ?int $unitsFrom = null,
    ) {
    }
}
