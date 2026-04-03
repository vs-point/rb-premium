<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class PostalAddress
{
    public function __construct(
        #[SerializedName('street')]
        public ?string $street = null,
        #[SerializedName('city')]
        public ?string $city = null,
        #[SerializedName('country')]
        public ?string $country = null,
    ) {
    }
}
