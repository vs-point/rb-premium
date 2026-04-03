<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

/** ISO 20022 charge bearer — who bears the transaction fees. */
enum ChargeBearer: string
{
    /** Debtor bears all charges */
    case Debt = 'DEBT';
    /** Creditor bears all charges */
    case Cred = 'CRED';
    /** Charges shared between debtor and creditor */
    case Shar = 'SHAR';
}
