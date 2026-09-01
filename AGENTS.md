# AGENTS.md

## Project Overview
This library adds SAP date/time format support on top of PHP core date classes.
Production code is intentionally minimal: two classes in `src/` and no runtime dependencies.
Part of [PHP/SAP](https://php-sap.github.io).

## Ecosystem

[PHP/SAP](https://php-sap.github.io) is split across five focused repositories that build on
each other instead of one monolithic package:

| Repository                  | Role                                                                                                    | Depends on (`composer.json`)                                  |
|-----------------------------|---------------------------------------------------------------------------------------------------------|---------------------------------------------------------------|
| `php-sap/interfaces`        | Contract-only interfaces (`IApi`, `IConfiguration`, `IFunction`, exceptions). No concrete classes.      | —                                                             |
| `php-sap/datetime`          | SAP date/time format support on top of native `DateTime`/`DateInterval`.                                | —                                                             |
| `php-sap/common`            | Generic abstract classes, API/config value objects, and exceptions implementing `interfaces`.           | `interfaces`, `datetime`                                      |
| `php-sap/integration-tests` | Shared abstract PHPUnit test infrastructure and SAP module mocks reused by concrete connector packages. | `interfaces`, `common`, `datetime`                            |
| `php-sap/saprfc-kralik`     | Concrete adapter for Gregor Kralik's `ext-sapnwrfc` extension.                                          | `interfaces`, `common` (+ `integration-tests` for tests only) |

**→ You are here: `php-sap/datetime`** — the SAP date/time formatting helper.

This package has no dependency on any other PHP/SAP package and no runtime dependencies at
all; it is consumed by `common` (and, transitively, by every connector) purely for SAP
date/time parsing. Keep it that way — don't introduce a dependency back into the ecosystem.

## Architecture

- `src/SapDateTime.php`: extends `DateTime` and centralizes SAP format handling.
- `src/SapDateTime.php`: `createFromFormat()` dispatches by SAP constants (`SAP_WEEK`, `SAP_DATE`, `SAP_TIME`, `SAP_TIMESTAMP`).
- `src/SapDateTime.php`: `createFromSapWeek()` validates with `SapDateTime::$sapWeekRegex` before parsing.
- `src/SapDateInterval.php`: extends `DateInterval` and overrides `createFromDateString()` to parse SAP `HHMMSS`.
- Design intent: keep native `DateTime`/`DateInterval` behavior while adding SAP-specific factories.

### Core Data Flow
- Input SAP string -> validate (regex for week/time) -> convert to PHP-compatible format -> return native-compatible object.
- Invalid SAP input returns `false` from factory methods (`DateTime|false`, `DateInterval|false`).
- Timezone support flows through `DateTimeZone|null` factory arguments in `SapDateTime`.

### Integration Points
- Upstream API surface: PHP `DateTime`, `DateInterval`, `DateTimeZone`.
- External libraries are dev-only (PHPUnit, PHP_CodeSniffer, PHPStan) via `composer.json`.
- Package namespace is `phpsap\DateTime\` (PSR-4), tests use `tests\phpsap\DateTime\`.

## Developer Workflows

All commands run through the `Makefile` via Docker, so the host machine does not need a
local PHP installation. Run `make help` for the full target list. Use PHP 8.1, 8.2, 8.3,
and 8.4 (matching the CI matrix in `.github/workflows/main.yml`) for anything
version-sensitive (PHPStan, PHP lint, tests). If you are behind a proxy, `install` and
`audit` already forward `HTTP_PROXY`/`HTTPS_PROXY`/`NO_PROXY`; pass
`CA_CERT_FILE=/path/to/ca.pem` to trust a corporate proxy root CA inside the container.

```bash
# Install/update dependencies for a given PHP version (set DEPENDENCIES_LOWEST=1 for
# --prefer-lowest, matching the CI "lowest" matrix job)
make install PHP_VERSION=8.1

# Run PHPUnit
make test PHP_VERSION=8.1

# Syntax-check every .php file in src/ and tests/, matches CI
make lint PHP_VERSION=8.1

# Run PHPStan
make analyze PHP_VERSION=8.1

# Auto-fix code style (run this before "sniff")
make beautify PHP_VERSION=8.1

# Check code style (uses phpcs.xml)
make sniff PHP_VERSION=8.1

# Check dependencies for known vulnerabilities
make audit

# Run composer validate --strict
make validate
```

**Always use these Makefile targets instead of inventing ad-hoc `docker run`/`composer`/
`php` commands.** If a task needs something the Makefile doesn't expose directly (e.g.
PHPUnit for a single test file/method), take the exact `docker run` invocation from the
matching Makefile target (image, `DOCKER_USER`, `DOCKER_MOUNT`, env forwarding) and only
append the extra arguments — don't build the command from scratch.

## Conventions
- Use strict typing in every PHP file: `declare(strict_types=1);`.
- Keep explicit return unions for factories (example: `DateTime|false` in `SapDateTime::createFromFormat`).
- Keep public SAP format constants on `SapDateTime` as the source of truth.
- Extend behavior via small static factories instead of introducing new service layers.
- Match test style in `tests/SapDateTimeTest.php` and `tests/SapDateIntervalTest.php`: data providers with broad valid/invalid cases.

## Safe Change Strategy for Agents
- Add a new SAP format by:
  1. adding a constant in `SapDateTime`,
  2. adding validation/parsing in `SapDateTime::createFromFormat()`,
  3. adding focused provider-driven tests in `tests/SapDateTimeTest.php`.
- Preserve compatibility with native PHP behavior and existing false-on-invalid semantics.
- Don't add a dependency on `interfaces`/`common`/any other PHP/SAP package; this library
  must stay usable standalone.
- Write documentation, comments, and new code in English to match the repository style.
- Always run QA/build commands through the `Makefile` targets, not self-invented `docker run`
  commands. For one-off variants (a single test, a single file), base the invocation on the
  relevant Makefile target and only append the extra arguments.

