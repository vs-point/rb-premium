<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Account;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class AccountListResponse
{
    /**
     * @param Account[] $accounts
     */
    public function __construct(
        #[SerializedName('accounts')]
        public array $accounts = [],
        #[SerializedName('page')]
        public ?int $page = null,
        #[SerializedName('size')]
        public ?int $size = null,
        #[SerializedName('first')]
        public ?bool $first = null,
        #[SerializedName('last')]
        public ?bool $last = null,
        #[SerializedName('totalPages')]
        public ?int $totalPages = null,
        #[SerializedName('totalSize')]
        public ?int $totalSize = null,
    ) {
    }
}
