<?php

declare(strict_types=1);

namespace VsPoint\RBPremium;

use VsPoint\RBPremium\Enum\Environment;

/**
 * File-based mTLS config — cert and key are loaded from PEM files on disk.
 */
final readonly class RBPremiumConfig implements RBPremiumConfigInterface
{
    /**
     * @param string      $clientId     Value for X-IBM-Client-Id header
     * @param string      $certPath     Path to PEM certificate file (may include private key)
     * @param string|null $certPassword Password for the certificate (if encrypted)
     * @param string|null $keyPath      Path to separate PEM private key file (if not bundled in certPath)
     * @param string|null $keyPassword  Password for the private key (if encrypted)
     * @param Environment $environment  Sandbox or Production
     */
    public function __construct(
        public string $clientId,
        public string $certPath,
        public ?string $certPassword = null,
        public ?string $keyPath = null,
        public ?string $keyPassword = null,
        public Environment $environment = Environment::Sandbox,
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getGuzzleCertOptions(): array
    {
        $options = [];

        $options['cert'] = $this->certPassword !== null
            ? [$this->certPath, $this->certPassword]
            : $this->certPath;

        if ($this->keyPath !== null) {
            $options['ssl_key'] = $this->keyPassword !== null
                ? [$this->keyPath, $this->keyPassword]
                : $this->keyPath;
        }

        return $options;
    }
}
