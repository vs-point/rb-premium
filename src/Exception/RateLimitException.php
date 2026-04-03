<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Exception;

/** HTTP 429 — too many requests; inspect $remainingDay / $remainingSecond for retry guidance. */
final class RateLimitException extends RBPremiumApiException
{
    public function __construct(
        string $message,
        string $responseBody,
        private readonly ?int $limitDay,
        private readonly ?int $limitSecond,
        private readonly ?int $remainingDay,
        private readonly ?int $remainingSecond,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $responseBody, $previous);
    }

    public function getLimitDay(): ?int
    {
        return $this->limitDay;
    }

    public function getLimitSecond(): ?int
    {
        return $this->limitSecond;
    }

    public function getRemainingDay(): ?int
    {
        return $this->remainingDay;
    }

    public function getRemainingSecond(): ?int
    {
        return $this->remainingSecond;
    }
}
