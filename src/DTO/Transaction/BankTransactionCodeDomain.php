<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class BankTransactionCodeDomain
{
    public function __construct(
        /**
         * e.g. PMNT (payments), LDAS (loans), SECU (securities)
         */
        #[SerializedName('code')]
        public ?string $code = null,
        #[SerializedName('family')]
        public ?BankTransactionCodeFamily $family = null,
    ) {
    }
}
