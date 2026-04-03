<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

enum StatementLine: string
{
    case Main = 'MAIN';
    case Additional = 'ADDITIONAL';
    case Mt940 = 'MT940';
}
