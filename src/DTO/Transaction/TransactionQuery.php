<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZoneRegion;
use InvalidArgumentException;

final readonly class TransactionQuery
{
    /**
     * How many days back the API returns transactions. An older `from` is rejected with HTTP 400 `DT01 Invalid date`.
     */
    public const MAX_HISTORY_DAYS = 90;

    /**
     * Timezone the bank evaluates dates in.
     */
    public const BANK_TIMEZONE = 'Europe/Prague';

    public function __construct(
        public LocalDate $from,
        public LocalDate $to,
        public ?int $page = null,
    ) {
    }

    /**
     * Range covering the last `$days` days up to today (bank timezone), inclusive.
     *
     * Always produces a range the API accepts, regardless of the current date — prefer this over
     * month arithmetic (`minusMonths(3)` spans 89–92 days depending on the calendar).
     *
     * @param int|null $days defaults to the longest allowed history
     * @param LocalDate|null $today override for "today" (mainly for tests)
     */
    public static function lastDays(?int $days = null, ?int $page = null, ?LocalDate $today = null): self
    {
        // one day of margin: "today" on our side may already be a day ahead/behind of the bank's
        $days ??= self::MAX_HISTORY_DAYS - 1;

        if ($days < 0 || $days >= self::MAX_HISTORY_DAYS) {
            throw new InvalidArgumentException(sprintf(
                'Transaction history is limited to %d days back, %d given.',
                self::MAX_HISTORY_DAYS - 1,
                $days,
            ));
        }

        $today ??= LocalDate::now(TimeZoneRegion::of(self::BANK_TIMEZONE));

        return new self(from: $today->minusDays($days), to: $today, page: $page);
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        $params = [
            'from' => (string) $this->from,
            'to' => (string) $this->to,
        ];

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        return $params;
    }
}
