<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Party
{
    public function __construct(
        #[SerializedName('name')]
        public ?string $name = null,
        #[SerializedName('postalAddress')]
        public ?PostalAddress $postalAddress = null,
        #[SerializedName('organisationIdentification')]
        public ?OrganisationIdentification $organisationIdentification = null,
        #[SerializedName('account')]
        public ?PartyAccount $account = null,
    ) {
    }
}
