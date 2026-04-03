<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use VsPoint\RBPremium\DTO\Payment\Batch;
use VsPoint\RBPremium\Enum\BatchStatus;

final class PaymentTest extends AbstractIntegrationTest
{
    public function testGetBatch(): void
    {
        $batchFileId = getenv('RB_PREMIUM_BATCH_FILE_ID');

        if ($batchFileId === false || $batchFileId === '') {
            self::markTestSkipped('Set RB_PREMIUM_BATCH_FILE_ID env var to an existing sandbox batch ID to run this test.');
        }

        $batch = $this->client->payments->getBatch((int) $batchFileId);

        self::assertInstanceOf(Batch::class, $batch);
        self::assertInstanceOf(BatchStatus::class, $batch->batchFileStatus);
        self::assertNotNull($batch->createDate);
        self::assertIsArray($batch->batchItems);
    }
}
