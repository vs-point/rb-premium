<?php

declare(strict_types=1);

namespace VsPoint\RBPremium;

use VsPoint\RBPremium\Enum\Environment;

/**
 * Inline mTLS config — cert and key are passed as PEM strings (not file paths).
 *
 * Uses CURLOPT_SSLCERT_BLOB / CURLOPT_SSLKEY_BLOB (curl 7.71+, PHP 8.1+)
 * so no temporary files are written to disk.
 *
 * Useful when credentials come from a secret manager, environment variable,
 * or any other in-memory source.
 */
final readonly class RBPremiumInlineConfig implements RBPremiumConfigInterface
{
    /**
     * @param string      $clientId     Value for X-IBM-Client-Id header
     * @param string      $certPem      PEM certificate contents (may include private key)
     * @param string|null $certPassword Password for the certificate (if encrypted)
     * @param string|null $keyPem       PEM private key contents (if not bundled in certPem)
     * @param string|null $keyPassword  Password for the private key (if encrypted)
     * @param Environment $environment  Sandbox or Production
     */
    public function __construct(
        public string $clientId,
        public string $certPem,
        public ?string $certPassword = null,
        public ?string $keyPem = null,
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
        $curlOptions = [
            CURLOPT_SSLCERT_BLOB => $this->certPem,
        ];

        if ($this->certPassword !== null) {
            $curlOptions[CURLOPT_SSLCERTPASSWD] = $this->certPassword;
        }

        if ($this->keyPem !== null) {
            $curlOptions[CURLOPT_SSLKEY_BLOB] = $this->keyPem;
        }

        if ($this->keyPassword !== null) {
            $curlOptions[CURLOPT_SSLKEYPASSWD] = $this->keyPassword;
        }

        return [
            'curl' => $curlOptions,
        ];
    }
}
