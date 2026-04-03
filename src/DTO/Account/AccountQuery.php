<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\DTO\Account;

final readonly class AccountQuery
{
    public function __construct(
        public ?int $page = null,
        public ?int $size = null,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        if ($this->size !== null) {
            $params['size'] = $this->size;
        }

        return $params;
    }
}
