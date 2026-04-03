<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service\Api;

use Brick\DateTime\LocalDate;
use Brick\Money\Currency;
use VsPoint\RBPremium\DTO\FxRate\FxRateListResponse;

final class FxRatesService extends AbstractRBPremiumService
{
    public function list(?LocalDate $date = null): FxRateListResponse
    {
        $query = $date !== null ? [
            'date' => (string) $date,
        ] : [];

        return $this->doGet('/fxrates', FxRateListResponse::class, $query);
    }

    public function get(Currency $currency, ?LocalDate $date = null): FxRateListResponse
    {
        $query = $date !== null ? [
            'date' => (string) $date,
        ] : [];

        return $this->doGet(sprintf('/fxrates/%s', $currency->getCurrencyCode()), FxRateListResponse::class, $query);
    }
}
