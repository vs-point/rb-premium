<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

/** ISO 20022 credit/debit indicator on a transaction entry. */
enum CreditDebitIndication: string
{
    /** Credit — incoming funds */
    case Credit = 'CRDT';
    /** Debit — outgoing funds */
    case Debit = 'DBIT';
}
