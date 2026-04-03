<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Payment;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class BatchImportResponse
{
    public function __construct(
        #[SerializedName('batchFileId')]
        public ?int $batchFileId = null,
    ) {
    }
}
