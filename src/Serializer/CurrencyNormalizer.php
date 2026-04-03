<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Serializer;

use Brick\Money\Currency;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CurrencyNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        assert($data instanceof Currency);

        return $data->getCurrencyCode();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Currency;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Currency
    {
        return Currency::of($data);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $type === Currency::class && is_string($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Currency::class => true,
        ];
    }
}
