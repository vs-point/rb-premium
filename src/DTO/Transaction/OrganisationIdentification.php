<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OrganisationIdentification
{
    public function __construct(
        #[SerializedName('name')]
        public ?string $name = null,
        #[SerializedName('bicOrBei')]
        public ?string $bicOrBei = null,
        #[SerializedName('bankCode')]
        public ?string $bankCode = null,
        #[SerializedName('address')]
        public ?PostalAddress $address = null,
    ) {
    }
}
