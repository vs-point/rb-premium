# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a standalone PHP library (`vs-point/rb-premium`) for communicating with the Raiffeisenbank Premium API (https://developers.rb.cz/premium/). It provides a typed PHP interface for working with bank accounts, transactions, payment batches, statements, and FX rates.

**No Symfony DI** — plain PHP library. No bundle, no container, no framework-bundle dependency. Symfony Serializer is used directly and configured via a static factory.

## Development Commands

**`composer` není na hostu nainstalovaný — všechny příkazy spouštěj přes Docker.**

```bash
# Instalace závislostí
docker run --rm -v "$(pwd):/app" -w /app vspoint/php:8.5-fpm-alpine sh -c "composer install --no-interaction --prefer-dist"

# PHPStan (level 6)
docker run --rm -v "$(pwd):/app" -w /app vspoint/php:8.5-fpm-alpine sh -c "./vendor/bin/phpstan analyse --memory-limit=512M"

# ECS — nejdřív auto-fix, pak kontrola
docker run --rm -v "$(pwd):/app" -w /app vspoint/php:8.5-fpm-alpine sh -c "./vendor/bin/ecs check --fix"
docker run --rm -v "$(pwd):/app" -w /app vspoint/php:8.5-fpm-alpine sh -c "./vendor/bin/ecs check"

# Integrační testy (vyžadují sandbox credentials)
docker run --rm -v "$(pwd):/app" -w /app vspoint/php:8.5-fpm-alpine sh -c "composer install --no-interaction && ./vendor/bin/phpunit --testdox"
docker run --rm -v "$(pwd):/app" -w /app vspoint/php:8.5-fpm-alpine sh -c "./vendor/bin/phpunit --testdox --filter FxRateTest"
```

**PHPStan a ECS musí projít čistě před každým commitem.** Spouštějí se automaticky v CI stage `analyse` před testy. Workflow: 1) `ecs --fix`, 2) `phpstan analyse`, 3) commit.

## Architecture

### No Symfony DI — Manual Serializer Setup

```php
// Recommended — static factory builds the pre-configured serializer
$config = new RBPremiumConfig(
    clientId: 'your-client-id',   // X-IBM-Client-Id header
    certPath: '/path/to/cert.pem',
    environment: Environment::Sandbox,
);
$client = RBPremiumClient::create($config);

// Custom serializer
$serializer = RBPremiumClient::createSerializer();
$client = new RBPremiumClient($config, $serializer);
```

### Sandbox vs Production

The `Environment` enum controls the URL path prefix:
- `Environment::Sandbox` → `/rbcz/premium/mock/...`
- `Environment::Production` → `/rbcz/premium/...`

```php
use VsPoint\RBPremium\Enum\Environment;

$config = new RBPremiumConfig(
    clientId: 'your-client-id',
    certPath: '/path/to/cert.pem',
    environment: Environment::Production, // or Environment::Sandbox
);
```

### Core Layers

1. **`RBPremiumConfig`** — immutable configuration: clientId (X-IBM-Client-Id), certPath/certPassword, keyPath/keyPassword (optional separate private key), environment.

2. **`RBPremiumClient`** — main entry point with sub-service properties:
   - `$client->accounts` — AccountsService
   - `$client->transactions` — TransactionsService
   - `$client->payments` — PaymentsService
   - `$client->statements` — StatementsService
   - `$client->fxRates` — FxRatesService

3. **`Service/RBPremiumHttpClient`** — internal Guzzle wrapper. Handles:
   - mTLS authentication (Guzzle `cert`/`ssl_key` options)
   - `X-IBM-Client-Id` header on every request
   - `X-Request-Id` auto-generated per request (`req-` + 16 random bytes as hex)
   - Environment-based path prefix prepended to all paths

4. **`Service/Api/AbstractRBPremiumService`** — base class with helpers:
   - `doGet(path, type, query)` — GET → deserialise JSON to type
   - `doPost(path, payload, responseType)` — serialise payload → POST JSON → deserialise response
   - `doPostBinary(path, payload)` — serialise payload → POST JSON → return raw bytes
   - `doPostFile(path, fileContent, contentType, responseType, extraHeaders)` — POST raw file → deserialise JSON response

5. **`Service/Api/*Service`** — one class per API endpoint group.

6. **`DTO/`** — organized per resource. All classes are `readonly` with constructor promotion and `#[SerializedName]`.

7. **`Enum/`** — typed enums for: `Environment`, `BatchImportFormat`, `BatchStatus`, `StatementLine`, `StatementFormat`, `BalanceType`.

8. **`Serializer/`** — custom Symfony normalizers: `BigDecimalNormalizer`, `LocalDateNormalizer`, `LocalDateTimeNormalizer`, `ZonedDateTimeNormalizer`.

### Serializer Setup (CRITICAL)

`createSerializer()` registers normalizers in this order — order matters:
1. `BackedEnumNormalizer` — handles PHP backed enums (e.g. `BatchStatus`, `BalanceType`)
2. `BigDecimalNormalizer` — handles `Brick\Math\BigDecimal`
3. `LocalDateNormalizer` — handles `Brick\DateTime\LocalDate`
4. `LocalDateTimeNormalizer` — handles `Brick\DateTime\LocalDateTime`
5. `ZonedDateTimeNormalizer` — handles `Brick\DateTime\ZonedDateTime`
6. `ArrayDenormalizer` — handles typed arrays
7. `ObjectNormalizer` with `MetadataAwareNameConverter` — handles DTOs

**CRITICAL**: `#[SerializedName]` only works when `MetadataAwareNameConverter` is passed to `ObjectNormalizer`. Without it, all JSON field name mappings are silently ignored.

### Money & Amounts

- `DTO/Shared/Amount` — wraps `BigDecimal $value` + `string $currency` with `toMoney(): Money` helper
- `ExchangeRate` — FX rates stored as `BigDecimal`, helper methods `getCurrencyFrom(): Currency`, `getCurrencyTo(): Currency`
- Never store amounts as plain `float`

### Adding a New Endpoint Group

1. Create `src/Service/Api/{Name}Service.php` extending `AbstractRBPremiumService`
2. Add DTOs in `src/DTO/{Name}/`
3. Add public readonly property + instantiation in `src/RBPremiumClient.php` constructor
4. Add integration test in `tests/Integration/{Name}Test.php`

### PHP File Requirements

- **Every PHP file MUST start with `declare(strict_types=1);`** immediately after `<?php`. No exceptions.

### Key Conventions

- All DTO properties use `readonly` constructor promotion with `#[SerializedName]` for API field names
- Nullable (`?Type`) for all optional API response fields
- `@param Type[] $field` PHPDoc is required on array properties so `PhpDocExtractor` can determine element type for deserialization
- `BackedEnumNormalizer` must be registered before `ObjectNormalizer`
- `BigDecimalNormalizer` must be registered before `ObjectNormalizer`

## RB Premium API

### Dokumentace

- **Sandbox spec (OpenAPI):** https://developers.rb.cz/premium/documentation/02rbczpremiumapi_sandbox — verze `1.1.20240910`
- **Obecná dokumentace:** https://developers.rb.cz/premium/

OpenAPI YAML je embeddovaný přímo v HTML stránce (načítá se přes `jsyaml.load()`), není dostupný jako statický `/swagger.json`. Pro stažení specifikace je nutné fetchnout samotnou stránku a extrahovat YAML obsah.

### Důležité poznatky ze specifikace

Tady jsou opravy, které by naivní implementace bez přečtení spec (nebo bez reálného testu) snadno zkazila:

**Transaction DTOs (ověřeno na reálném sandbox response):**
- **`entryDetails` je SINGLE OBJECT** (`?EntryDetails`), ne pole — OpenAPI spec je zavádějící, sandbox vrací jeden objekt
- **`relatedParties`**, **`remittanceInformation`**, **`instructedAmount`**, **`chargeBearer`** jsou přímo v `transactionDetails`, ne v `entryDetails`
- **`chargeBearer`** patří do `TransactionDetails`, ne do `References`
- **`instructedAmount`** patří do `TransactionDetails`, ne do `References`
- **`paymentCardNumber`** je na `Transaction`, ne v `TransactionDetails`
- **`bookingDate`** a **`valueDate`** jsou ISO 8601 datetime s timezone (např. `2020-04-28T07:59:05.000+02:00`) → `ZonedDateTime`, ne `LocalDate`
- **`instructedAmount`** má navíc pole `exchangeRate` (BigDecimal) → vlastní DTO `InstructedAmount`, ne sdílený `Amount`
- **`ChargeBearer`** enum: pouze `DEBT`, `CRED`, `SHAR` — `SLEV` v tomto API není
- **`bankTransactionCode`** je objekt s polem `code` (string), ne string

**Account DTOs:**
- **`accountId`** a **`accountTypeId`** jsou `int`, ne string
- **`mainCurrency`** je v sandboxu `null` — nelze spoléhat; pro zjištění měny použij balance endpoint
- **Accounts sandbox vrací 2 účty a ignoruje parametr `size`** — testování paginace počtem výsledků nefunguje

**FxRate DTOs:**
- **`effectiveDateFrom`**, **`effectiveDateTo`**, **`tradingDate`** jsou ISO 8601 datetime (ne LocalDate) → `ZonedDateTime`

**Balance DTOs:**
- **`Balance`** má flat strukturu `value` (BigDecimal) + `currency` (string), ne vnořený `Amount` objekt. Metoda `toMoney()` je přímo na `Balance`.

**brick/date-time:**
- **`LocalDate::now()`** vyžaduje argument `TimeZone` — `LocalDate::now(TimeZone::utc())`

### Authentication

- **mTLS** — Guzzle `cert` + `ssl_key` options with PEM files
- **`X-IBM-Client-Id`** — header sent on every request (from `RBPremiumConfig::$clientId`)
- **`X-Request-Id`** — auto-generated per-request, max 60 chars, pattern `[a-zA-Z0-9\-_:]{1,60}`

Converting PKCS#12 to PEM (sandbox cert password: `Test12345678`):
```bash
openssl pkcs12 -in cert.p12 -out cert.pem -nodes -passin pass:Test12345678
```

### Endpoints

| Service | Method | HTTP | Path |
|---------|--------|------|------|
| `accounts` | `list(?AccountQuery)` | GET | `/accounts` |
| `accounts` | `balance(accountNumber)` | GET | `/accounts/{accountNumber}/balance` |
| `transactions` | `list(accountNumber, currencyCode, TransactionQuery)` | GET | `/accounts/{accountNumber}/{currencyCode}/transactions` |
| `payments` | `importBatch(fileContent, format, ...)` | POST | `/payments/batches` |
| `payments` | `getBatch(batchFileId)` | GET | `/payments/batches/{batchFileId}` |
| `statements` | `list(StatementListPayload)` | POST | `/accounts/statements` |
| `statements` | `download(StatementDownloadPayload)` | POST | `/accounts/statements/download` |
| `fxRates` | `list(?LocalDate)` | GET | `/fxrates` |
| `fxRates` | `get(currencyCode, ?LocalDate)` | GET | `/fxrates/{currencyCode}` |

### Enums

| Enum | Values |
|------|--------|
| `Environment` | `Sandbox`, `Production` |
| `BatchImportFormat` | `GeminiP11`, `GeminiP32`, `GeminiF84`, `AboKpc`, `DomXml`, `SepaXml`, `Cfd`, `Cfu`, `Cfa` |
| `BatchStatus` | `Draft`, `Error`, `ForSign`, `Verified`, `PassingToBank`, `Passed`, `PassedToBankWithError`, `Undisclosed` |
| `StatementLine` | `Main`, `Additional`, `Mt940` |
| `StatementFormat` | `Pdf`, `Xml`, `Mt940` |
| `BalanceType` | `Clav` (available), `Clbd` (booked), `Clab`, `Blck` (blocked) |

### Exceptions

Všechny výjimky dědí z `RBPremiumApiException` (lze tedy chytat genericky). `getHttpStatus()` vrací HTTP status, `getResponseBody()` raw tělo odpovědi.

| HTTP | Třída | Kdy |
|------|-------|-----|
| 400 | `InvalidRequestException` | Neplatné parametry (např. DT01 — špatný formát data) |
| 401 | `UnauthorisedException` | Neplatný/chybějící certifikát nebo X-IBM-Client-Id |
| 403 | `InsufficientRightsException` | Přístup k danému zdroji není povolen |
| 404 | `NotFoundException` | Účet, dávka nebo výpis neexistuje |
| 429 | `RateLimitException` | Překročen rate limit; `getRemainingDay()` / `getRemainingSecond()` pro retry logiku |
| ostatní | `RBPremiumApiException` | Neočekávaná chyba |

```php
use VsPoint\RBPremium\Exception\NotFoundException;
use VsPoint\RBPremium\Exception\RateLimitException;
use VsPoint\RBPremium\Exception\RBPremiumApiException;

try {
    $balance = $client->accounts->balance($accountNumber);
} catch (NotFoundException $e) {
    // účet neexistuje
} catch (RateLimitException $e) {
    // $e->getRemainingDay(), $e->getRemainingSecond()
} catch (RBPremiumApiException $e) {
    // $e->getHttpStatus(), $e->getResponseBody()
}
```

### Rate Limiting

Production responses include `X-RateLimit-*` headers. `RateLimitException` automatically extracts `limitDay`, `limitSecond`, `remainingDay`, `remainingSecond` from these headers.

## Integration Tests

Tests use sandbox environment. Required environment variables:

```bash
RB_PREMIUM_CLIENT_ID=your-sandbox-client-id
RB_PREMIUM_CERT_PATH=/path/to/cert.pem
RB_PREMIUM_CERT_PASSWORD=        # optional, if cert is password-protected
RB_PREMIUM_KEY_PATH=             # optional, if private key is in a separate file
RB_PREMIUM_KEY_PASSWORD=         # optional, if key is password-protected
```

Tests skip automatically if `RB_PREMIUM_CLIENT_ID` or `RB_PREMIUM_CERT_PATH` are not set.

## Compatibility

- PHP >= 8.2
- Symfony Serializer 6.4, 7.x, 8.x
- No dependency on Symfony Framework / DI container
