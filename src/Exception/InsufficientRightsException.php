<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Exception;

/** HTTP 403 — authenticated but not authorised for the requested resource. */
final class InsufficientRightsException extends RBPremiumApiException
{
}
