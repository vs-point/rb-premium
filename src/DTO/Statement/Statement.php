<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Statement;

use Brick\DateTime\LocalDate;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Statement
{
    /**
     * @param string[] $formats
     */
    public function __construct(
        #[SerializedName('statementId')]
        public ?string $statementId = null,
        #[SerializedName('statementNumber')]
        public ?string $statementNumber = null,
        #[SerializedName('dateFrom')]
        public ?LocalDate $dateFrom = null,
        #[SerializedName('dateTo')]
        public ?LocalDate $dateTo = null,
        #[SerializedName('formats')]
        public array $formats = [],
    ) {
    }
}
