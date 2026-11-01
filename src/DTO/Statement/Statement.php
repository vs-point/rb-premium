<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Statement;

use Brick\DateTime\LocalDate;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Statement
{
    /**
     * @param string[] $formats dostupné formáty, jak je vrací RB (`PDF`, `XML`, `MT940`)
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
        #[SerializedName('statementFormats')]
        public array $formats = [],
    ) {
    }
}
