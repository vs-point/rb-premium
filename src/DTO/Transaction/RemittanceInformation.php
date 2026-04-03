<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class RemittanceInformation
{
    public function __construct(
        #[SerializedName('unstructured')]
        public ?string $unstructured = null,
        #[SerializedName('creditorReferenceInformation')]
        public ?CreditorReferenceInformation $creditorReferenceInformation = null,
        #[SerializedName('originatorMessage')]
        public ?string $originatorMessage = null,
    ) {
    }
}
