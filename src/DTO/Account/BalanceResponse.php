<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Account;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class BalanceResponse
{
    /**
     * @param CurrencyFolder[] $currencyFolders
     */
    public function __construct(
        #[SerializedName('numberPart1')]
        public ?string $numberPart1 = null,
        #[SerializedName('numberPart2')]
        public ?string $numberPart2 = null,
        #[SerializedName('bankCode')]
        public ?string $bankCode = null,
        #[SerializedName('currencyFolders')]
        public array $currencyFolders = [],
    ) {
    }
}
