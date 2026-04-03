<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Statement;

use Brick\DateTime\LocalDate;
use Brick\Money\Currency;
use Symfony\Component\Serializer\Attribute\SerializedName;
use VsPoint\RBPremium\Enum\StatementLine;

final readonly class StatementListPayload
{
    public function __construct(
        #[SerializedName('accountNumber')]
        public string $accountNumber,
        #[SerializedName('currency')]
        public ?Currency $currency = null,
        #[SerializedName('statementLine')]
        public ?StatementLine $statementLine = null,
        #[SerializedName('dateFrom')]
        public ?LocalDate $dateFrom = null,
        #[SerializedName('dateTo')]
        public ?LocalDate $dateTo = null,
    ) {
    }
}
