<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Serializer;

use Brick\DateTime\ZonedDateTime;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ZonedDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        assert($object instanceof ZonedDateTime);

        return (string) $object;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ZonedDateTime;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ZonedDateTime
    {
        return ZonedDateTime::parse((string) $data);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $type === ZonedDateTime::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ZonedDateTime::class => true,
        ];
    }
}
