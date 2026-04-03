<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Statement;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class StatementListResponse
{
    /**
     * @param Statement[] $statements
     */
    public function __construct(
        #[SerializedName('statements')]
        public array $statements = [],
    ) {
    }
}
