<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class References
{
    public function __construct(
        #[SerializedName('endToEndIdentification')]
        public ?string $endToEndIdentification = null,
    ) {
    }
}
