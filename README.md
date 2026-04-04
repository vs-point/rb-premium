# vs-point/rb-premium

PHP knihovna pro komunikaci s [Raiffeisenbank Premium API](https://developers.rb.cz/premium/). Typovaný interface pro bankovní účty, transakce, platební dávky, výpisy a devizové kurzy.

## Instalace

```bash
composer require vs-point/rb-premium
```
## Konfigurace

Autentizace probíhá přes **mTLS** (klientský certifikát) + hlavičku `X-IBM-Client-Id`.

### Převod PKCS#12 (.p12) na PEM

Knihovna očekává certifikát ve formátu PEM. Pokud máš od banky soubor `.p12`, převeď ho pomocí `openssl`:

```bash
# Heslo zadáš interaktivně (bezpečnější pro produkci)
openssl pkcs12 -in cert.p12 -out cert.pem -nodes

# Heslo inline (vhodné pro CI/CD scripty)
openssl pkcs12 -in cert.p12 -out cert.pem -nodes -passin pass:HESLO
```

Přepínač `-nodes` znamená, že privátní klíč nebude v PEM zašifrovaný (no DES). Pro produkci lze klíč ponechat zašifrovaný — pak je `-nodes` vynechat a heslo předat přes `certPassword`:

```bash
# PEM s heslem na klíči
openssl pkcs12 -in cert.p12 -out cert.pem -passin pass:HESLO -passout pass:NOVE_HESLO
```

Certifikát a klíč lze také rozdělit do dvou souborů (parametry `certPath` + `keyPath`):

```bash
openssl pkcs12 -in cert.p12 -nokeys -out cert.pem -passin pass:HESLO
openssl pkcs12 -in cert.p12 -nocerts -nodes -out key.pem -passin pass:HESLO
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

### Inline certifikát (PEM string)

Pokud certifikát nepochází ze souboru na disku — například je uložený v secret manageru, proměnné prostředí nebo databázi — použij `RBPremiumInlineConfig`. Certifikát se předá jako PEM string přímo do curlu přes `CURLOPT_SSLCERT_BLOB`, **žádný dočasný soubor se nezapisuje**.

```php
use VsPoint\RBPremium\RBPremiumClient;
use VsPoint\RBPremium\RBPremiumInlineConfig;
use VsPoint\RBPremium\Enum\Environment;

$config = new RBPremiumInlineConfig(
    clientId: 'your-client-id',
    certPem: getenv('RB_CERT_PEM'),        // PEM string certifikátu (může obsahovat i klíč)
    certPassword: null,                    // heslo certifikátu (pokud šifrovaný)
    keyPem: getenv('RB_KEY_PEM'),          // PEM string klíče zvlášť (pokud není v certPem)
    keyPassword: null,
    environment: Environment::Production,
);

$client = RBPremiumClient::create($config);
```

> `CURLOPT_SSLCERT_BLOB` vyžaduje curl ≥ 7.71 a PHP ≥ 8.1. Obě podmínky splňuje jakákoliv aktuální instalace PHP 8.2+.

### Přímé použití .p12 souboru

PHP umí PKCS#12 načíst nativně bez jakékoliv knihovny přes `openssl_pkcs12_read()`:

```php
use VsPoint\RBPremium\RBPremiumClient;
use VsPoint\RBPremium\RBPremiumInlineConfig;
use VsPoint\RBPremium\Enum\Environment;

$p12Content = file_get_contents('/path/to/cert.p12');

if (!openssl_pkcs12_read($p12Content, $certs, 'heslo-k-p12')) {
    throw new \RuntimeException('Nepodařilo se načíst .p12 certifikát: ' . openssl_error_string());
}

$config = new RBPremiumInlineConfig(
    clientId: 'your-client-id',
    certPem: $certs['cert'],
    keyPem: $certs['pkey'],
    environment: Environment::Production,
);

$client = RBPremiumClient::create($config);
```

> **Omezení `openssl_pkcs12_read()`:** Funkce má problémy s certifikáty šifrovanými legacy algoritmy (RC2, 3DES), které jsou časté u starších Windows exportů. Na serverech s OpenSSL ≥ 3.x takový soubor selže s chybou bez dalšího vysvětlení. V takovém případě proveď jednorázovou konverzi přes `openssl` příkazový řádek (viz sekce výše) a výsledný PEM ulož mimo disk (secret manager, env var).

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
    echo $account->iban . ' — ' . $account->mainCurrency?->getCurrencyCode() . PHP_EOL;
}

// Stránkování
use VsPoint\RBPremium\DTO\Account\AccountQuery;

$result = $client->accounts->list(new AccountQuery(page: 1, size: 20));

// Zůstatky účtu
$balance = $client->accounts->balance('1234567890');

foreach ($balance->currencyFolders as $folder) {
    echo $folder->currency?->getCurrencyCode() . ':' . PHP_EOL;
    foreach ($folder->balances as $item) {
        echo '  ' . $item->balanceType?->value . ': ' . $item->toMoney() . PHP_EOL;
    }
}
```

### Transakce

```php
use Brick\DateTime\LocalDate;
use Brick\Money\Currency;
use VsPoint\RBPremium\DTO\Transaction\TransactionQuery;

$result = $client->transactions->list(
    accountNumber: '1234567890',
    currency: Currency::of('CZK'),
    query: new TransactionQuery(
        from: LocalDate::of(2024, 1, 1),
        to: LocalDate::of(2024, 1, 31),
        page: 1,
    ),
);

foreach ($result->transactions as $tx) {
    $money = $tx->amount?->toMoney();
    echo $tx->bookingDate . ' ' . $money . ' ' . $tx->creditDebitIndication?->value . PHP_EOL;

    $ref = $tx->entryDetails?->transactionDetails?->remittanceInformation?->creditorReferenceInformation;
    if ($ref !== null) {
        echo '  VS: ' . $ref->variable . ', KS: ' . $ref->constant . PHP_EOL;
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
use Brick\Money\Currency;

// Aktuální kurzy
$result = $client->fxRates->list();

// Kurzy k datu
$result = $client->fxRates->list(LocalDate::of(2024, 1, 15));

// Kurz konkrétní měny
$result = $client->fxRates->get(Currency::of('EUR'));

foreach ($result->exchangeRateLists as $list) {
    foreach ($list->exchangeRates as $rate) {
        echo $rate->currencyFrom?->getCurrencyCode() . '/' . $rate->currencyTo?->getCurrencyCode()
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
| `transactions` | `list(accountNumber, Currency, TransactionQuery)` | GET `/accounts/{accountNumber}/{currencyCode}/transactions` |
| `payments` | `importBatch(...)` | POST `/payments/batches` |
| `payments` | `getBatch(batchFileId)` | GET `/payments/batches/{batchFileId}` |
| `statements` | `list(StatementListPayload)` | POST `/accounts/statements` |
| `statements` | `download(StatementDownloadPayload)` | POST `/accounts/statements/download` |
| `fxRates` | `list(?LocalDate)` | GET `/fxrates` |
| `fxRates` | `get(Currency, ?LocalDate)` | GET `/fxrates/{currencyCode}` |
