<?php

declare(strict_types=1);

namespace VsPoint\RBPremium\Tests\Integration;

use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;
use VsPoint\RBPremium\DTO\Statement\Statement;
use VsPoint\RBPremium\DTO\Statement\StatementDownloadPayload;
use VsPoint\RBPremium\DTO\Statement\StatementListPayload;
use VsPoint\RBPremium\Enum\StatementFormat;
use VsPoint\RBPremium\Enum\StatementLine;

final class StatementTest extends AbstractIntegrationTest
{
    private string $accountNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $accounts = $this->client->accounts->list();

        if ($accounts->accounts === []) {
            self::markTestSkipped('No accounts available in sandbox.');
        }

        self::assertNotNull($accounts->accounts[0]->accountNumber);
        $this->accountNumber = $accounts->accounts[0]->accountNumber;
    }

    public function testListReturnsStatements(): void
    {
        $result = $this->client->statements->list(new StatementListPayload(
            accountNumber: $this->accountNumber,
        ));

        self::assertIsArray($result->statements);
        self::assertContainsOnlyInstancesOf(Statement::class, $result->statements);
    }

    public function testListStatementHasExpectedFields(): void
    {
        $result = $this->client->statements->list(new StatementListPayload(
            accountNumber: $this->accountNumber,
        ));

        if ($result->statements === []) {
            self::markTestSkipped('No statements available in sandbox.');
        }

        $statement = $result->statements[0];
        self::assertNotNull($statement->statementId);
        self::assertNotNull($statement->dateFrom);
        self::assertNotNull($statement->dateTo);
        self::assertTrue(
            $statement->dateTo->isAfterOrEqualTo($statement->dateFrom),
            'Statement dateTo should be after or equal to dateFrom.',
        );
    }

    public function testListFilteredByDateRange(): void
    {
        $result = $this->client->statements->list(new StatementListPayload(
            accountNumber: $this->accountNumber,
            dateFrom: LocalDate::now(TimeZone::utc())->minusMonths(3),
            dateTo: LocalDate::now(TimeZone::utc()),
        ));

        self::assertIsArray($result->statements);
    }

    public function testListFilteredByStatementLine(): void
    {
        $result = $this->client->statements->list(new StatementListPayload(
            accountNumber: $this->accountNumber,
            statementLine: StatementLine::Main,
        ));

        self::assertIsArray($result->statements);
    }

    public function testDownloadPdf(): void
    {
        $statements = $this->client->statements->list(new StatementListPayload(
            accountNumber: $this->accountNumber,
        ));

        if ($statements->statements === []) {
            self::markTestSkipped('No statements available to download in sandbox.');
        }

        $statement = $statements->statements[0];
        self::assertNotNull($statement->statementId);

        $content = $this->client->statements->download(new StatementDownloadPayload(
            accountNumber: $this->accountNumber,
            statementId: $statement->statementId,
            statementFormat: StatementFormat::Pdf,
        ));

        self::assertNotEmpty($content, 'Downloaded statement PDF should not be empty.');
        // PDF magic bytes
        self::assertStringStartsWith('%PDF', $content, 'Downloaded content should be a valid PDF.');
    }

    public function testDownloadXml(): void
    {
        $statements = $this->client->statements->list(new StatementListPayload(
            accountNumber: $this->accountNumber,
        ));

        if ($statements->statements === []) {
            self::markTestSkipped('No statements available to download in sandbox.');
        }

        $statement = $statements->statements[0];
        self::assertNotNull($statement->statementId);

        $content = $this->client->statements->download(new StatementDownloadPayload(
            accountNumber: $this->accountNumber,
            statementId: $statement->statementId,
            statementFormat: StatementFormat::Xml,
        ));

        self::assertNotEmpty($content);
        self::assertStringStartsWith('<?xml', $content, 'Downloaded content should be valid XML.');
    }
}
