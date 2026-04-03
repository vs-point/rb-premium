<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\FxRate;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class FxRateListResponse
{
    /**
     * @param ExchangeRateList[] $exchangeRateLists
     */
    public function __construct(
        #[SerializedName('exchangeRateLists')]
        public array $exchangeRateLists = [],
    ) {
    }
}
