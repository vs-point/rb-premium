<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Brick\DateTime\LocalDate;

final readonly class TransactionQuery
{
    public function __construct(
        public LocalDate $from,
        public LocalDate $to,
        public ?int $page = null,
    ) {
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
