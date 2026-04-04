<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use VsPoint\RBPremium\Exception\InsufficientRightsException;
use VsPoint\RBPremium\Exception\InvalidRequestException;
use VsPoint\RBPremium\Exception\NotFoundException;
use VsPoint\RBPremium\Exception\RateLimitException;
use VsPoint\RBPremium\Exception\RBPremiumApiException;
use VsPoint\RBPremium\Exception\UnauthorisedException;
use VsPoint\RBPremium\RBPremiumConfigInterface;

final class RBPremiumHttpClient
{
    private const BASE_URI = 'https://api.rb.cz';

    private readonly Client $client;

    private readonly string $pathPrefix;

    public function __construct(RBPremiumConfigInterface $config)
    {
        $this->pathPrefix = $config->getEnvironment()
            ->value;

        $guzzleOptions = array_merge(
            [
                'base_uri' => self::BASE_URI,
                'headers' => [
                    'X-IBM-Client-Id' => $config->getClientId(),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ],
            $config->getGuzzleCertOptions(),
        );

        $this->client = new Client($guzzleOptions);
    }

    /**
     * @param array<string, string|int> $query
     */
    public function get(string $path, array $query = []): ResponseInterface
    {
        return $this->request('GET', $path, [
            'query' => $query,
        ]);
    }

    public function post(string $path, string $jsonBody): ResponseInterface
    {
        return $this->request('POST', $path, [
            'body' => $jsonBody,
        ]);
    }

    /**
     * POST with raw file content (e.g. payment batch file) and custom headers.
     *
     * @param array<string, string> $extraHeaders
     */
    public function postFile(
        string $path,
        string $fileContent,
        string $contentType,
        array $extraHeaders = []
    ): ResponseInterface {
        return $this->request('POST', $path, [
            'body' => $fileContent,
            'headers' => array_merge([
                'Content-Type' => $contentType,
            ], $extraHeaders),
        ]);
    }

    /**
     * POST JSON body, return raw binary response (e.g. statement download).
     */
    public function postForBinary(string $path, string $jsonBody): string
    {
        $response = $this->request('POST', $path, [
            'body' => $jsonBody,
            'headers' => [
                'Accept' => '*/*',
            ],
        ]);

        return (string) $response->getBody();
    }

    private function headerInt(ResponseInterface $response, string $name): ?int
    {
        $value = $response->getHeaderLine($name);

        return $value !== '' ? (int) $value : null;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $path, array $options = []): ResponseInterface
    {
        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            [
                'X-Request-Id' => 'req-' . bin2hex(random_bytes(16)),
            ],
        );

        try {
            return $this->client->request($method, $this->pathPrefix . $path, $options);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
            $message = sprintf('RB Premium API error: %s %s — HTTP %d', $method, $path, $status);

            throw match ($status) {
                400 => new InvalidRequestException($message, 400, $body, $e),
                401 => new UnauthorisedException($message, 401, $body, $e),
                403 => new InsufficientRightsException($message, 403, $body, $e),
                404 => new NotFoundException($message, 404, $body, $e),
                429 => new RateLimitException(
                    $message,
                    $body,
                    limitDay: $this->headerInt($response, 'X-RateLimit-Limit-Day'),
                    limitSecond: $this->headerInt($response, 'X-RateLimit-Limit-Second'),
                    remainingDay: $this->headerInt($response, 'X-RateLimit-Remaining-Day'),
                    remainingSecond: $this->headerInt($response, 'X-RateLimit-Remaining-Second'),
                    previous: $e,
                ),
                default => new RBPremiumApiException($message, $status, $body, $e),
            };
        }
    }
}
