<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Payment;

use Brick\DateTime\LocalDateTime;
use Brick\Math\BigDecimal;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class BatchItem
{
    public function __construct(
        #[SerializedName('accountId')]
        public ?string $accountId = null,
        #[SerializedName('numberOfPayments')]
        public ?int $numberOfPayments = null,
        #[SerializedName('sumAmount')]
        public ?BigDecimal $sumAmount = null,
        #[SerializedName('sumAmountCurrencyId')]
        public ?string $sumAmountCurrencyId = null,
        #[SerializedName('batchType')]
        public ?string $batchType = null,
        #[SerializedName('status')]
        public ?string $status = null,
        #[SerializedName('assignedUserName')]
        public ?string $assignedUserName = null,
        #[SerializedName('lastChangeDateTime')]
        public ?LocalDateTime $lastChangeDateTime = null,
    ) {
    }
}
