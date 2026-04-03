<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

enum StatementFormat: string
{
    case Pdf = 'pdf';
    case Xml = 'xml';
    case Mt940 = 'MT940';
}
