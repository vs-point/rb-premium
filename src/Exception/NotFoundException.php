<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Exception;

/** HTTP 404 — requested resource (account, batch, statement…) does not exist. */
final class NotFoundException extends RBPremiumApiException
{
}
