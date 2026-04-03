<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Exception;

final class UnableToParseBodyFromResponse extends \RuntimeException
{
    public function __construct(string $body, \Throwable $previous)
    {
        parent::__construct(
            sprintf('Unable to parse response body: %s', substr($body, 0, 500)),
            0,
            $previous,
        );
    }
}
