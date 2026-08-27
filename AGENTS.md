# AGENTS.md - php-sap/datetime

## Quick Overview
This library adds SAP date/time format support on top of PHP core date classes.
Production code is intentionally minimal: two classes in `src/` and no runtime dependencies.

## Architecture That Matters
- `src/SapDateTime.php`: extends `DateTime` and centralizes SAP format handling.
- `src/SapDateTime.php`: `createFromFormat()` dispatches by SAP constants (`SAP_WEEK`, `SAP_DATE`, `SAP_TIME`, `SAP_TIMESTAMP`).
- `src/SapDateTime.php`: `createFromSapWeek()` validates with `SapDateTime::$sapWeekRegex` before parsing.
- `src/SapDateInterval.php`: extends `DateInterval` and overrides `createFromDateString()` to parse SAP `HHMMSS`.
- Design intent: keep native `DateTime`/`DateInterval` behavior while adding SAP-specific factories.

## Core Data Flow
- Input SAP string -> validate (regex for week/time) -> convert to PHP-compatible format -> return native-compatible object.
- Invalid SAP input returns `false` from factory methods (`DateTime|false`, `DateInterval|false`).
- Timezone support flows through `DateTimeZone|null` factory arguments in `SapDateTime`.

## Developer Workflow (Repo-Local)

All commands run inside official PHP Docker images so the host machine does not need a
local PHP installation. Use PHP 8.1, 8.2, 8.3 and 8.4 (matching the CI matrix in
`.github/workflows/main.yml`) for anything version-sensitive (PHPStan, PHP lint).
If you are behind a proxy, forward `HTTP_PROXY`/`HTTPS_PROXY`/`NO_PROXY` into the
container whenever the command needs network access (e.g. `composer install`).

```bash
# Install/update dependencies (needs network access -> forward proxy settings)
docker run --rm --init --interactive --tty \
  --user "$(id -u)":"$(id -g)" \
  --env HTTP_PROXY --env HTTPS_PROXY --env NO_PROXY \
  --volume "$(pwd)":/app --workdir /app \
  composer:2 install

# Run tests (no network access needed)
docker run --rm --init \
  --user "$(id -u)":"$(id -g)" \
  --volume "$(pwd)":/app --workdir /app \
  php:8.1-cli php vendor/bin/phpunit

# Fix code style (run first, no network access needed)
docker run --rm --init \
  --user "$(id -u)":"$(id -g)" \
  --volume "$(pwd)":/app --workdir /app \
  php:8.1-cli php vendor/bin/phpcbf

# Check remaining style issues (no network access needed)
docker run --rm --init \
  --user "$(id -u)":"$(id -g)" \
  --volume "$(pwd)":/app --workdir /app \
  php:8.1-cli php vendor/bin/phpcs

# Run static analysis for every supported PHP version (no network access needed;
# --memory-limit=-1 works around the image's low default memory_limit)
for PHP_VERSION in 8.1 8.2 8.3 8.4; do
  docker run --rm --init \
    --user "$(id -u)":"$(id -g)" \
    --volume "$(pwd)":/app --workdir /app \
    "php:${PHP_VERSION}-cli" php vendor/bin/phpstan analyse --memory-limit=-1
done
```

## Conventions You Should Follow Here
- Use strict typing in every PHP file: `declare(strict_types=1);`.
- Keep explicit return unions for factories (example: `DateTime|false` in `SapDateTime::createFromFormat`).
- Keep public SAP format constants on `SapDateTime` as the source of truth.
- Extend behavior via small static factories instead of introducing new service layers.
- Match test style in `tests/SapDateTimeTest.php` and `tests/SapDateIntervalTest.php`: data providers with broad valid/invalid cases.

## Integration Points
- Upstream API surface: PHP `DateTime`, `DateInterval`, `DateTimeZone`.
- External libraries are dev-only (PHPUnit, PHP_CodeSniffer, PHPStan) via `composer.json`.
- Package namespace is `phpsap\DateTime\` (PSR-4), tests use `tests\phpsap\DateTime\`.

## Safe Extension Pattern
- Add a new SAP format by:
  1. adding a constant in `SapDateTime`,
  2. adding validation/parsing in `SapDateTime::createFromFormat()`,
  3. adding focused provider-driven tests in `tests/SapDateTimeTest.php`.
- Preserve compatibility with native PHP behavior and existing false-on-invalid semantics.

