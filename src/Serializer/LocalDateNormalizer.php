<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Serializer;

use Brick\DateTime\LocalDate;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LocalDateNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        assert($object instanceof LocalDate);

        return (string) $object;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof LocalDate;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): LocalDate
    {
        return LocalDate::parse((string) $data);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $type === LocalDate::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            LocalDate::class => true,
        ];
    }
}
