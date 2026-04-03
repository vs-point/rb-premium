<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Enum;

enum Environment: string
{
    case Sandbox = '/rbcz/premium/mock';
    case Production = '/rbcz/premium';
}
