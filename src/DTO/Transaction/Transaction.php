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
        /**
         * Maskované číslo karty, jak ho posílá sandbox. Produkce ho posílá v `entryDetails.transactionDetails`
         * — číst přes {@see self::cardNumber()}.
         */
        #[SerializedName('paymentCardNumber')]
        public ?string $paymentCardNumber = null,
        #[SerializedName('bankTransactionCode')]
        public ?BankTransactionCode $bankTransactionCode = null,
        #[SerializedName('entryDetails')]
        public ?EntryDetails $entryDetails = null,
    ) {
    }

    /**
     * Maskované číslo karty (platba kartou, výběr z bankomatu, poplatky ke kartě) bez ohledu na to,
     * kde ho RB poslalo: produkce v `entryDetails.transactionDetails`, sandbox na transakci.
     */
    public function cardNumber(): ?string
    {
        $number = $this->entryDetails->transactionDetails->paymentCardNumber ?? $this->paymentCardNumber;

        return $number !== null && $number !== '' ? $number : null;
    }
}
