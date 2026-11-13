# SAP DateTime

[![License: MIT][license-mit]](LICENSE)

Extends PHP's [DateTime][datetime] class by SAP week, date, time and timestamp
format. Extends PHP's [DateInterval][dateinterval] class by SAP time format.

* SAP week in format `<year><week>`
* SAP date in format `<year><month><day>`
* SAP time in format `<hour><minute><second>`
* SAP timestamp in format `<year><month><day><hour><minute><second>`

## Usage

```
composer require php-sap/datetime
```

### Parse a SAP week string into a DateTime object. 

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_WEEK, '201846');
echo $dateTime->format('o \w\e\ek W') . PHP_EOL;
/**
 * Output: 2018 week 46
 */
```

### Format a DateTime object as SAP week string

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = new SapDateTime('2018-10-19 08:09:10');
echo $dateTime->format(SapDateTime::SAP_WEEK) . PHP_EOL;
/**
 * Output: 201842
 */
```

### Parse a SAP date string into a DateTime object

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_DATE, '20181101');
echo $dateTime->format('Y-m-d') . PHP_EOL;
/**
 * Output: 2018-11-01
 */
```

### Format a DateTime object as SAP date

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = new SapDateTime('2018-12-31 09:10:11');
echo $dateTime->format(SapDateTime::SAP_DATE) . PHP_EOL;
/**
 * Output: 20181231
 */
```

### Parse a SAP time string into a DateTime object

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_TIME, '132001');
echo $dateTime->format('H:i:s') . PHP_EOL;
/**
 * Output: 13:20:01
 */
```

### Parse a SAP time string into a DateInterval object

This example uses 25 hours to demonstrate the usage of a DateInterval object
in combination with a DateTime object. Please beware, that the `format()`
method is more like PHP's `sprintf()` and less like `date()`.

```php
<?php
use phpsap\DateTime\SapDateInterval;
$time = SapDateInterval::createFromDateString('252003');
echo $time->format('%H:%I:%S') . PHP_EOL;
/**
 * Output: 25:20:03
 */
$date = new DateTime('2018-11-01 00:00:00');
$date->add($time);
echo $date->format('Y-m-d H:i:s');
/**
 * Output: 2018-11-02 01:20:03
 */
```

### Format a DateTime object as SAP time

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = new SapDateTime('21:45:05');
echo $dateTime->format(SapDateTime::SAP_TIME) . PHP_EOL;
/**
 * Output: 214505
 */
```

### Format a DateInterval object as SAP time

```php
<?php
use phpsap\DateTime\SapDateInterval;
$time = new SapDateInterval('PT21H45M05S');
echo $time->format(SapDateInterval::SAP_TIME) . PHP_EOL;
/**
 * Output: 214505
 */
```

### Parse a SAP timestamp into a DateTime object

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_TIMESTAMP, '20181019080910');
echo $dateTime->format('Y-m-d H:i:s') . PHP_EOL;
/**
 * Output: 2018-10-19 08:09:10
 */
```

### Format a DateTime object as SAP timestamp

```php
<?php
use phpsap\DateTime\SapDateTime;
$dateTime = new SapDateTime('2018-12-31 09:10:11');
echo $dateTime->format(SapDateTime::SAP_TIMESTAMP) . PHP_EOL;
/**
 * Output: 20181231091011
 */
```

## Development

Development commands run through the Docker-backed `Makefile`; PHP need not be
installed locally. PHP `^8.1` is supported. CI checks PHP 8.1 and 8.5 with
normal and lowest dependencies, while retaining the existing PHP 8.2–8.4
checks during the required-check transition. Run `make help` for all targets.

```sh
make check PHP_VERSION=8.1
make check PHP_VERSION=8.5 DEPENDENCIES_LOWEST=1
```

`check` resolves dependencies, runs lint, PHPUnit with Xdebug coverage, PHPStan
and PHPCS, then audits that same temporary lockfile. `install` removes its
temporary lockfile, so a later standalone `audit` does not verify that earlier
snapshot. `clean` removes `vendor/` and the PHPUnit cache, not a pre-existing
lockfile. Composer uses an isolated manifest copy in the project root and does
not modify the checked-in Composer configuration.

The host needs Docker, `curl`, `sha256sum` and `flock`. Install, check, audit
and clean serialize on the manifest to protect the shared `vendor/` directory.
`check` downloads the pinned
Xdebug source and verifies its SHA-256 before building it inside the target PHP
container. The Composer executable comes from `COMPOSER_IMAGE` (default
`composer:2`); Composer and QA run under the requested PHP image. The container
installs `git` and `unzip` for dependency resolution. Proxy environment variables
and an optional `CA_CERT_FILE` are forwarded, and `COMPOSER_CACHE_DIR` controls
the cache. If `config.platform.php` is present, only the isolated manifest copy
uses the actual PHP patch version; other platform settings and the checked-in
manifest remain unchanged.

Changes to `main` use pull requests and squash merges. Issue branches should be
named `<issue-number>-<title-slug>`. The force-push ruleset excludes the broader
glob `[0-9]*-*`, which also matches some names outside that convention; it does
not enforce the PR source. Main is protected separately.

[license-mit]: https://img.shields.io/badge/license-MIT-blue.svg
[datetime]: https://www.php.net/manual/en/class.datetime.php
[dateinterval]: https://www.php.net/manual/en/class.dateinterval.php
