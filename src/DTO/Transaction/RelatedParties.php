<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class RelatedParties
{
    public function __construct(
        #[SerializedName('counterParty')]
        public ?Party $counterParty = null,
        #[SerializedName('intermediaryInstitution')]
        public ?Party $intermediaryInstitution = null,
        #[SerializedName('ultimateCounterParty')]
        public ?Party $ultimateCounterParty = null,
    ) {
    }
}
