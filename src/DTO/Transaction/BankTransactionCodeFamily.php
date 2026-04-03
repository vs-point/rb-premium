<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class BankTransactionCodeFamily
{
    public function __construct(
        /**
         * e.g. RCDT (received credit transfer), ICDT (issued credit transfer)
         */
        #[SerializedName('code')]
        public ?string $code = null,
        /**
         * e.g. VCOM (various), SALA (salary)
         */
        #[SerializedName('subFamilyCode')]
        public ?string $subFamilyCode = null,
    ) {
    }
}
