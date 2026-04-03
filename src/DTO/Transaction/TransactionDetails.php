<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;
use VsPoint\RBPremium\Enum\ChargeBearer;

final readonly class TransactionDetails
{
    public function __construct(
        #[SerializedName('references')]
        public ?References $references = null,
        #[SerializedName('instructedAmount')]
        public ?InstructedAmount $instructedAmount = null,
        #[SerializedName('chargeBearer')]
        public ?ChargeBearer $chargeBearer = null,
        #[SerializedName('relatedParties')]
        public ?RelatedParties $relatedParties = null,
        #[SerializedName('remittanceInformation')]
        public ?RemittanceInformation $remittanceInformation = null,
    ) {
    }
}
