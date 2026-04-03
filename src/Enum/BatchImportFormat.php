<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

enum BatchImportFormat: string
{
    case GeminiP11 = 'GEMINI-P11';
    case GeminiP32 = 'GEMINI-P32';
    case GeminiF84 = 'GEMINI-F84';
    case AboKpc = 'ABO-KPC';
    case DomXml = 'DOM-XML';
    case SepaXml = 'SEPA-XML';
    case Cfd = 'CFD';
    case Cfu = 'CFU';
    case Cfa = 'CFA';
}
