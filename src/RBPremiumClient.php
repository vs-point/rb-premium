<?php

declare(strict_types=1);

namespace VsPoint\RBPremium;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use VsPoint\RBPremium\Serializer\BigDecimalNormalizer;
use VsPoint\RBPremium\Serializer\CurrencyNormalizer;
use VsPoint\RBPremium\Serializer\LocalDateNormalizer;
use VsPoint\RBPremium\Serializer\LocalDateTimeNormalizer;
use VsPoint\RBPremium\Serializer\ZonedDateTimeNormalizer;
use VsPoint\RBPremium\Service\Api\AccountsService;
use VsPoint\RBPremium\Service\Api\FxRatesService;
use VsPoint\RBPremium\Service\Api\PaymentsService;
use VsPoint\RBPremium\Service\Api\StatementsService;
use VsPoint\RBPremium\Service\Api\TransactionsService;
use VsPoint\RBPremium\Service\RBPremiumHttpClient;

final class RBPremiumClient
{
    public readonly AccountsService $accounts;

    public readonly TransactionsService $transactions;

    public readonly PaymentsService $payments;

    public readonly StatementsService $statements;

    public readonly FxRatesService $fxRates;

    public function __construct(RBPremiumConfig $config, SerializerInterface $serializer)
    {
        $httpClient = new RBPremiumHttpClient($config);

        $this->accounts = new AccountsService($httpClient, $serializer);
        $this->transactions = new TransactionsService($httpClient, $serializer);
        $this->payments = new PaymentsService($httpClient, $serializer);
        $this->statements = new StatementsService($httpClient, $serializer);
        $this->fxRates = new FxRatesService($httpClient, $serializer);
    }

    public static function create(RBPremiumConfig $config): self
    {
        return new self($config, self::createSerializer());
    }

    public static function createSerializer(): SerializerInterface
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $propertyInfo = new PropertyInfoExtractor(
            typeExtractors: [new PhpDocExtractor(), new ReflectionExtractor()],
        );

        $normalizers = [
            new BackedEnumNormalizer(),
            new BigDecimalNormalizer(),
            new CurrencyNormalizer(),
            new LocalDateNormalizer(),
            new LocalDateTimeNormalizer(),
            new ZonedDateTimeNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                classMetadataFactory: $classMetadataFactory,
                nameConverter: $nameConverter,
                propertyTypeExtractor: $propertyInfo,
            ),
        ];

        return new Serializer($normalizers, [
            'json' => new JsonEncoder(),
        ]);
    }
}
