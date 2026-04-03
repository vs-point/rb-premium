<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Brick\DateTime\ZonedDateTime;
use Symfony\Component\Serializer\Attribute\SerializedName;
use VsPoint\RBPremium\DTO\Shared\Amount;
use VsPoint\RBPremium\Enum\CreditDebitIndication;

final readonly class Transaction
{
    public function __construct(
        #[SerializedName('entryReference')]
        public ?string $entryReference = null,
        #[SerializedName('amount')]
        public ?Amount $amount = null,
        #[SerializedName('creditDebitIndication')]
        public ?CreditDebitIndication $creditDebitIndication = null,
        #[SerializedName('bookingDate')]
        public ?ZonedDateTime $bookingDate = null,
        #[SerializedName('valueDate')]
        public ?ZonedDateTime $valueDate = null,
        #[SerializedName('paymentCardNumber')]
        public ?string $paymentCardNumber = null,
        #[SerializedName('bankTransactionCode')]
        public ?BankTransactionCode $bankTransactionCode = null,
        #[SerializedName('entryDetails')]
        public ?EntryDetails $entryDetails = null,
    ) {
    }
}
