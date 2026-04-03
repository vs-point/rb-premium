<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Exception;

/** HTTP 401 — invalid or missing client certificate / X-IBM-Client-Id. */
final class UnauthorisedException extends RBPremiumApiException
{
}
