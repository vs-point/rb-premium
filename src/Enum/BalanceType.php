<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

/** CLAV = available, CLBD = booked, CLAB = blocked, BLCK = blocked */
enum BalanceType: string
{
    case Clav = 'CLAV';
    case Clbd = 'CLBD';
    case Clab = 'CLAB';
    case Blck = 'BLCK';
}
