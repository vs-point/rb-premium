<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service\Api;

use VsPoint\RBPremium\DTO\Statement\StatementDownloadPayload;
use VsPoint\RBPremium\DTO\Statement\StatementListPayload;
use VsPoint\RBPremium\DTO\Statement\StatementListResponse;

final class StatementsService extends AbstractRBPremiumService
{
    public function list(StatementListPayload $payload): StatementListResponse
    {
        return $this->doPost('/accounts/statements', $payload, StatementListResponse::class);
    }

    /**
     * Download a statement file. Returns raw binary content (PDF, XML or MT940).
     */
    public function download(StatementDownloadPayload $payload): string
    {
        return $this->doPostBinary('/accounts/statements/download', $payload);
    }
}
