<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Unit;

use Brick\DateTime\LocalDate;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VsPoint\RBPremium\DTO\Transaction\TransactionQuery;

/**
 * API vrací transakce jen MAX_HISTORY_DAYS dní zpět (starší `from` → 400 DT01 Invalid date).
 * Testy nesmí záviset na dnešním datu — kontrolují rozsah pro každý den v několika letech (vč. přestupného).
 */
final class TransactionQueryTest extends TestCase
{
    public function testDefaultRangeIsWithinApiLimitForEveryDayOfTheYear(): void
    {
        $day = LocalDate::of(2027, 1, 1);
        $end = LocalDate::of(2029, 1, 1); // 2028 je přestupný

        while ($day->isBefore($end)) {
            $query = TransactionQuery::lastDays(today: $day);

            self::assertTrue($query->to->isEqualTo($day));
            self::assertLessThan(
                TransactionQuery::MAX_HISTORY_DAYS,
                $query->from->daysUntil($query->to),
                sprintf('Range for %s exceeds the API history limit.', $day),
            );

            $day = $day->plusDays(1);
        }
    }

    public function testDefaultRangeUsesNearlyWholeAllowedHistory(): void
    {
        $query = TransactionQuery::lastDays(today: LocalDate::of(2026, 11, 1));

        self::assertSame('2026-08-04', (string) $query->from);
        self::assertSame('2026-11-01', (string) $query->to);
    }

    public function testExplicitDaysAndPage(): void
    {
        $query = TransactionQuery::lastDays(days: 7, page: 2, today: LocalDate::of(2026, 3, 3));

        self::assertSame([
            'from' => '2026-02-24',
            'to' => '2026-03-03',
            'page' => 2,
        ], $query->toArray());
    }

    public function testWithoutTodayUsesCurrentDate(): void
    {
        $query = TransactionQuery::lastDays();

        self::assertLessThan(TransactionQuery::MAX_HISTORY_DAYS, $query->from->daysUntil($query->to));
    }

    #[DataProvider('invalidDays')]
    public function testRejectsDaysOutsideApiLimit(int $days): void
    {
        $this->expectException(InvalidArgumentException::class);

        TransactionQuery::lastDays(days: $days);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidDays(): iterable
    {
        yield 'negative' => [-1];
        yield 'limit itself' => [TransactionQuery::MAX_HISTORY_DAYS];
        yield 'three months' => [92];
    }
}
