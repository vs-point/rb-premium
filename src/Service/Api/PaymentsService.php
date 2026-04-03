<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Service\Api;

use VsPoint\RBPremium\DTO\Payment\Batch;
use VsPoint\RBPremium\DTO\Payment\BatchImportResponse;
use VsPoint\RBPremium\Enum\BatchImportFormat;

final class PaymentsService extends AbstractRBPremiumService
{
    /**
     * Import a payment batch file.
     *
     * @param string            $fileContent      Raw content of the payment file
     * @param BatchImportFormat $format           File format
     * @param string|null       $batchName        Optional batch name (max 50 chars)
     * @param bool              $combinedPayments Merge payments to the same counterparty
     * @param bool              $autocorrect      Auto-correct minor errors (default: true)
     */
    public function importBatch(
        string $fileContent,
        BatchImportFormat $format,
        ?string $batchName = null,
        bool $combinedPayments = false,
        bool $autocorrect = true,
    ): BatchImportResponse {
        $extraHeaders = [
            'Batch-Import-Format' => $format->value,
            'Batch-Combined-Payments' => $combinedPayments ? 'true' : 'false',
            'Batch-Autocorrect' => $autocorrect ? 'true' : 'false',
        ];

        if ($batchName !== null) {
            $extraHeaders['Batch-Name'] = $batchName;
        }

        return $this->doPostFile(
            '/payments/batches',
            $fileContent,
            'application/octet-stream',
            BatchImportResponse::class,
            $extraHeaders,
        );
    }

    public function getBatch(int $batchFileId): Batch
    {
        return $this->doGet(sprintf('/payments/batches/%d', $batchFileId), Batch::class);
    }
}
