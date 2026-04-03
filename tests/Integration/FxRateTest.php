<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;
use Brick\DateTime\ZonedDateTime;
use Brick\Math\BigDecimal;
use Brick\Money\Currency;
use VsPoint\RBPremium\DTO\FxRate\ExchangeRate;
use VsPoint\RBPremium\DTO\FxRate\ExchangeRateList;

final class FxRateTest extends AbstractIntegrationTest
{
    public function testListReturnsExchangeRateLists(): void
    {
        $result = $this->client->fxRates->list();

        self::assertNotEmpty($result->exchangeRateLists);
        self::assertContainsOnlyInstancesOf(ExchangeRateList::class, $result->exchangeRateLists);
    }

    public function testListExchangeRateListHasRates(): void
    {
        $result = $this->client->fxRates->list();
        $list = $result->exchangeRateLists[0];

        self::assertNotEmpty($list->exchangeRates);
        self::assertContainsOnlyInstancesOf(ExchangeRate::class, $list->exchangeRates);
    }

    public function testListRatesAreBigDecimal(): void
    {
        $result = $this->client->fxRates->list();

        foreach ($result->exchangeRateLists as $list) {
            foreach ($list->exchangeRates as $rate) {
                self::assertInstanceOf(BigDecimal::class, $rate->exchangeRateBuy);
                self::assertInstanceOf(BigDecimal::class, $rate->exchangeRateSell);
                self::assertInstanceOf(BigDecimal::class, $rate->exchangeRateCenter);

                self::assertTrue(
                    $rate->exchangeRateSell->isGreaterThanOrEqualTo($rate->exchangeRateBuy),
                    sprintf('Sell rate should be >= buy rate for %s/%s.', $rate->currencyFrom, $rate->currencyTo),
                );
            }
        }
    }

    public function testListDatesAreZonedDateTime(): void
    {
        $result = $this->client->fxRates->list();
        $list = $result->exchangeRateLists[0];

        self::assertInstanceOf(ZonedDateTime::class, $list->tradingDate);
        self::assertInstanceOf(ZonedDateTime::class, $list->effectiveDateFrom);
    }

    public function testListForSpecificDate(): void
    {
        $date = LocalDate::now(TimeZone::utc())->minusDays(1);
        $result = $this->client->fxRates->list($date);

        self::assertNotEmpty($result->exchangeRateLists);
    }

    public function testGetSpecificCurrencyReturnsRates(): void
    {
        $result = $this->client->fxRates->get(Currency::of('EUR'));

        self::assertNotEmpty($result->exchangeRateLists);

        $rates = $result->exchangeRateLists[0]->exchangeRates;
        self::assertNotEmpty($rates);

        $currencies = array_map(static fn (ExchangeRate $r) => $r->currencyFrom?->getCurrencyCode(), $rates);
        self::assertContains('EUR', $currencies, 'Response for EUR should contain EUR rate.');
    }

    public function testGetSpecificCurrencyWithDate(): void
    {
        $date = LocalDate::now(TimeZone::utc())->minusDays(1);
        $result = $this->client->fxRates->get(Currency::of('USD'), $date);

        self::assertNotEmpty($result->exchangeRateLists);
    }

    public function testCurrencyFromAndToAreCurrencyObjects(): void
    {
        $result = $this->client->fxRates->get(Currency::of('EUR'));
        $rate = $result->exchangeRateLists[0]->exchangeRates[0];

        self::assertInstanceOf(Currency::class, $rate->currencyFrom);
        self::assertInstanceOf(Currency::class, $rate->currencyTo);
    }
}
