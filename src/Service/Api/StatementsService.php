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
     *
     * RB vyžaduje hlavičku `Accept-Language` (bez ní HTTP 400 „Accept-Language: validation.error.NotEmpty“),
     * podle ní je jazyk výpisu. `statementId` z {@see self::list()} platí na **jedno** stažení (druhé, i v jiném formátu,
     * končí 400 „Statement id does not exist“) — pro každý formát znovu zavolat list().
     *
     * @param string $language jazyk výpisu (cs, en)
     */
    public function download(StatementDownloadPayload $payload, string $language = 'cs'): string
    {
        return $this->doPostBinary('/accounts/statements/download', $payload, [
            'Accept-Language' => $language,
        ]);
    }
}
