<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service\Api;

use Symfony\Component\Serializer\SerializerInterface;
use VsPoint\RBPremium\Exception\UnableToParseBodyFromResponse;
use VsPoint\RBPremium\Service\RBPremiumHttpClient;

abstract class AbstractRBPremiumService
{
    public function __construct(
        protected readonly RBPremiumHttpClient $client,
        protected readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * GET request deserialised to $type.
     *
     * @template T of object
     * @param class-string<T> $type
     * @param array<string, string|int> $query
     * @return T
     */
    protected function doGet(string $path, string $type, array $query = []): object
    {
        $response = $this->client->get($path, $query);
        $body = (string) $response->getBody();

        try {
            return $this->serializer->deserialize($body, $type, 'json');
        } catch (\Throwable $e) {
            throw new UnableToParseBodyFromResponse($body, $e);
        }
    }

    /**
     * POST JSON payload, deserialise JSON response to $responseType.
     *
     * @template T of object
     * @param class-string<T> $responseType
     * @return T
     */
    protected function doPost(string $path, object $payload, string $responseType): object
    {
        $jsonBody = $this->serializer->serialize($payload, 'json');
        $response = $this->client->post($path, $jsonBody);
        $body = (string) $response->getBody();

        try {
            return $this->serializer->deserialize($body, $responseType, 'json');
        } catch (\Throwable $e) {
            throw new UnableToParseBodyFromResponse($body, $e);
        }
    }

    /**
     * POST JSON payload, return raw binary response (e.g. statement file download).
     */
    protected function doPostBinary(string $path, object $payload): string
    {
        $jsonBody = $this->serializer->serialize($payload, 'json');

        return $this->client->postForBinary($path, $jsonBody);
    }

    /**
     * POST raw file content, deserialise JSON response to $responseType.
     *
     * @template T of object
     * @param class-string<T> $responseType
     * @param array<string, string> $extraHeaders
     * @return T
     */
    protected function doPostFile(
        string $path,
        string $fileContent,
        string $contentType,
        string $responseType,
        array $extraHeaders = []
    ): object {
        $response = $this->client->postFile($path, $fileContent, $contentType, $extraHeaders);
        $body = (string) $response->getBody();

        try {
            return $this->serializer->deserialize($body, $responseType, 'json');
        } catch (\Throwable $e) {
            throw new UnableToParseBodyFromResponse($body, $e);
        }
    }
}
