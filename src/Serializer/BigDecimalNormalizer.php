<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Serializer;

use Brick\Math\BigDecimal;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BigDecimalNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        assert($data instanceof BigDecimal);

        return (string) $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof BigDecimal;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): BigDecimal
    {
        return BigDecimal::of($data);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $type === BigDecimal::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BigDecimal::class => true,
        ];
    }
}
