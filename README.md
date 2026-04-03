# vs-point/rb-premium

PHP knihovna pro komunikaci s [Raiffeisenbank Premium API](https://developers.rb.cz/premium/). Typovaný interface pro bankovní účty, transakce, platební dávky, výpisy a devizové kurzy.

**Standalone knihovna — bez závislosti na Symfony DI kontejneru.**

## Instalace

### Z lokálního adresáře

```json
"repositories": [{"type": "path", "url": "lib/rb-premium"}]
```
```bash
composer require vs-point/rb-premium:@dev
```

### Z Git repozitáře

```bash
composer config repositories.rb-premium git git@git.vs-point.cz:vspoint/package/rb-premium.git
composer require vs-point/rb-premium:^1.0
```

## Konfigurace

Autentizace probíhá přes **mTLS** (klientský certifikát) + hlavičku `X-IBM-Client-Id`.

Převod PKCS#12 na PEM (heslo sandbox certifikátu: `Test12345678`):
```bash
openssl pkcs12 -in cert.p12 -out cert.pem -nodes -passin pass:Test12345678
```

```php
use VsPoint\RBPremium\RBPremiumClient;
use VsPoint\RBPremium\RBPremiumConfig;
use VsPoint\RBPremium\Enum\Environment;

$config = new RBPremiumConfig(
    clientId: 'your-client-id',           // X-IBM-Client-Id
    certPath: '/path/to/cert.pem',        // PEM certifikát (může obsahovat i klíč)
    certPassword: null,                   // heslo certifikátu (pokud šifrovaný)
    keyPath: null,                        // PEM klíč zvlášť (pokud není v certPath)
    keyPassword: null,
    environment: Environment::Sandbox,    // nebo Environment::Production
);

$client = RBPremiumClient::create($config);
```

### Integrace se Symfony DI

```yaml
# config/services.yaml
services:
    VsPoint\RBPremium\RBPremiumConfig:
        arguments:
            $clientId: '%env(RB_PREMIUM_CLIENT_ID)%'
            $certPath: '%env(RB_PREMIUM_CERT_PATH)%'
            $environment: !php/enum VsPoint\RBPremium\Enum\Environment::Production

    VsPoint\RBPremium\RBPremiumClient:
        factory: ['VsPoint\RBPremium\RBPremiumClient', 'create']
        arguments:
            $config: '@VsPoint\RBPremium\RBPremiumConfig'
```

## Použití

### Bankovní účty

```php
// Výpis účtů
$result = $client->accounts->list();

foreach ($result->accounts as $account) {
    echo $account->iban . ' — ' . $account->mainCurrency . PHP_EOL;
}

// Stránkování
use VsPoint\RBPremium\DTO\Account\AccountQuery;

$result = $client->accounts->list(new AccountQuery(page: 1, size: 20));

// Zůstatky účtu
$balance = $client->accounts->balance('1234567890');

foreach ($balance->currencyFolders as $folder) {
    echo $folder->currency . ':' . PHP_EOL;
    foreach ($folder->balances as $item) {
        echo '  ' . $item->balanceType?->value . ': ' . $item->value?->toMoney() . PHP_EOL;
    }
}
```

### Transakce

```php
use Brick\DateTime\LocalDate;
use VsPoint\RBPremium\DTO\Transaction\TransactionQuery;

$result = $client->transactions->list(
    accountNumber: '1234567890',
    currencyCode: 'CZK',
    query: new TransactionQuery(
        from: LocalDate::of(2024, 1, 1),
        to: LocalDate::of(2024, 1, 31),
        page: 1,
    ),
);

foreach ($result->transactions as $tx) {
    $money = $tx->amount?->toMoney();
    echo $tx->bookingDate . ' ' . $money . ' ' . $tx->creditDebitIndication . PHP_EOL;

    $remittance = $tx->entryDetails?->remittanceInformation;
    if ($remittance !== null) {
        echo '  VS: ' . $remittance->variable . ', KS: ' . $remittance->constant . PHP_EOL;
    }
}
```

### Platební dávky

```php
use VsPoint\RBPremium\Enum\BatchImportFormat;

// Import dávky
$fileContent = file_get_contents('/path/to/payments.xml');

$response = $client->payments->importBatch(
    fileContent: $fileContent,
    format: BatchImportFormat::SepaXml,
    batchName: 'Platby leden 2024',
    autocorrect: true,
);

echo 'Batch ID: ' . $response->batchFileId . PHP_EOL;

// Stav dávky
$batch = $client->payments->getBatch($response->batchFileId);
echo 'Stav: ' . $batch->batchFileStatus?->value . PHP_EOL;
```

### Výpisy

```php
use Brick\DateTime\LocalDate;
use VsPoint\RBPremium\DTO\Statement\StatementDownloadPayload;
use VsPoint\RBPremium\DTO\Statement\StatementListPayload;
use VsPoint\RBPremium\Enum\StatementFormat;
use VsPoint\RBPremium\Enum\StatementLine;

// Seznam výpisů
$result = $client->statements->list(new StatementListPayload(
    accountNumber: '1234567890',
    statementLine: StatementLine::Main,
    dateFrom: LocalDate::of(2024, 1, 1),
    dateTo: LocalDate::of(2024, 1, 31),
));

foreach ($result->statements as $statement) {
    echo $statement->statementId . ' (' . $statement->dateFrom . ' – ' . $statement->dateTo . ')' . PHP_EOL;
}

// Stažení výpisu jako PDF
$pdfContent = $client->statements->download(new StatementDownloadPayload(
    accountNumber: '1234567890',
    statementId: $result->statements[0]->statementId,
    statementFormat: StatementFormat::Pdf,
));

file_put_contents('/tmp/vypis.pdf', $pdfContent);
```

### Devizové kurzy

```php
use Brick\DateTime\LocalDate;

// Aktuální kurzy
$result = $client->fxRates->list();

// Kurzy k datu
$result = $client->fxRates->list(LocalDate::of(2024, 1, 15));

// Kurz konkrétní měny
$result = $client->fxRates->get('EUR');

foreach ($result->exchangeRateLists as $list) {
    foreach ($list->exchangeRates as $rate) {
        echo $rate->currencyFrom . '/' . $rate->currencyTo
            . ' nákup: ' . $rate->exchangeRateBuy
            . ' prodej: ' . $rate->exchangeRateSell
            . PHP_EOL;
    }
}
```

## Přehled endpointů

| Service | Metoda | Endpoint |
|---------|--------|----------|
| `accounts` | `list(?AccountQuery)` | GET `/accounts` |
| `accounts` | `balance(accountNumber)` | GET `/accounts/{accountNumber}/balance` |
| `transactions` | `list(accountNumber, currencyCode, TransactionQuery)` | GET `/accounts/{accountNumber}/{currencyCode}/transactions` |
| `payments` | `importBatch(...)` | POST `/payments/batches` |
| `payments` | `getBatch(batchFileId)` | GET `/payments/batches/{batchFileId}` |
| `statements` | `list(StatementListPayload)` | POST `/accounts/statements` |
| `statements` | `download(StatementDownloadPayload)` | POST `/accounts/statements/download` |
| `fxRates` | `list(?LocalDate)` | GET `/fxrates` |
| `fxRates` | `get(currencyCode, ?LocalDate)` | GET `/fxrates/{currencyCode}` |
