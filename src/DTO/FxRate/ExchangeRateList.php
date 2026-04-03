<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\FxRate;

use Brick\DateTime\ZonedDateTime;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ExchangeRateList
{
    /**
     * @param ExchangeRate[] $exchangeRates
     */
    public function __construct(
        #[SerializedName('effectiveDateFrom')]
        public ?ZonedDateTime $effectiveDateFrom = null,
        #[SerializedName('effectiveDateTo')]
        public ?ZonedDateTime $effectiveDateTo = null,
        #[SerializedName('tradingDate')]
        public ?ZonedDateTime $tradingDate = null,
        #[SerializedName('ordinalNumber')]
        public ?int $ordinalNumber = null,
        #[SerializedName('lastRates')]
        public ?bool $lastRates = null,
        #[SerializedName('exchangeRates')]
        public array $exchangeRates = [],
    ) {
    }
}
