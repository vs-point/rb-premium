<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Serializer;

use Brick\DateTime\LocalDateTime;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LocalDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        assert($object instanceof LocalDateTime);

        return (string) $object;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LocalDateTime;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): LocalDateTime
    {
        return LocalDateTime::parse((string) $data);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $type === LocalDateTime::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            LocalDateTime::class => true,
        ];
    }
}
