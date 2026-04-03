<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class CreditorReferenceInformation
{
    public function __construct(
        #[SerializedName('variable')]
        public ?string $variable = null,
        #[SerializedName('constant')]
        public ?string $constant = null,
        #[SerializedName('specific')]
        public ?string $specific = null,
    ) {
    }
}
