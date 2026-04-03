<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

enum BatchStatus: string
{
    case Draft = 'DRAFT';
    case Error = 'ERROR';
    case ForSign = 'FOR_SIGN';
    case Verified = 'VERIFIED';
    case PassingToBank = 'PASSING_TO_BANK';
    case Passed = 'PASSED';
    case PassedToBankWithError = 'PASSED_TO_BANK_WITH_ERROR';
    case Undisclosed = 'UNDISCLOSED';
}
