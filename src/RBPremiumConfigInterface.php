<?php

declare(strict_types=1);

namespace VsPoint\RBPremium;

use VsPoint\RBPremium\Enum\Environment;

interface RBPremiumConfigInterface
{
    public function getClientId(): string;

    public function getEnvironment(): Environment;

    /**
     * Returns Guzzle client options for mTLS authentication (cert + key).
     *
     * File-based config returns ['cert' => ..., 'ssl_key' => ...].
     * Inline config returns ['curl' => [CURLOPT_SSLCERT_BLOB => ..., ...]].
     *
     * @return array<string, mixed>
     */
    public function getGuzzleCertOptions(): array;
}
