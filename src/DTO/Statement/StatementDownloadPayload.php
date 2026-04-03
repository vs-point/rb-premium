<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Statement;

use Brick\Money\Currency;
use Symfony\Component\Serializer\Attribute\SerializedName;
use VsPoint\RBPremium\Enum\StatementFormat;

final readonly class StatementDownloadPayload
{
    public function __construct(
        #[SerializedName('accountNumber')]
        public string $accountNumber,
        #[SerializedName('statementId')]
        public string $statementId,
        #[SerializedName('statementFormat')]
        public StatementFormat $statementFormat,
        #[SerializedName('currency')]
        public ?Currency $currency = null,
    ) {
    }
}
