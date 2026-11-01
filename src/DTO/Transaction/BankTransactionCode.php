<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Transaction;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * Kód bankovní transakce.
 *
 * RB (sandbox i produkce, ověřeno 2026-11-01 na produkčním účtu) posílá jen `{"code": "30000201000"}` —
 * vlastní 11místný kód, ze kterého plyne „Kategorie transakce“ na výpisu (Platba, Platba kartou, Poplatek,
 * Úrok, Vklad/Výběr z bankomatu…). ISO 20022 struktura `domain` / `proprietary` ze specifikace v odpovědi
 * není; pole zůstávají pro případ, že ji RB začne posílat.
 */
final readonly class BankTransactionCode
{
    public function __construct(
        /**
         * RB kód transakce, např. 10000101000 (odchozí úhrada), 30000201000 (platba kartou), 40000605000 (poplatek)
         */
        #[SerializedName('code')]
        public ?string $code = null,
        #[SerializedName('domain')]
        public ?BankTransactionCodeDomain $domain = null,
        #[SerializedName('proprietary')]
        public ?BankTransactionCodeProprietary $proprietary = null,
    ) {
    }
}
