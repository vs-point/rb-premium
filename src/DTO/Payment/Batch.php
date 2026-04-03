<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Payment;

use Brick\DateTime\LocalDate;
use Symfony\Component\Serializer\Attribute\SerializedName;
use VsPoint\RBPremium\Enum\BatchStatus;

final readonly class Batch
{
    /**
     * @param BatchItem[] $batchItems
     */
    public function __construct(
        #[SerializedName('batchName')]
        public ?string $batchName = null,
        #[SerializedName('batchFileStatus')]
        public ?BatchStatus $batchFileStatus = null,
        #[SerializedName('createDate')]
        public ?LocalDate $createDate = null,
        #[SerializedName('batchItems')]
        public array $batchItems = [],
    ) {
    }
}
