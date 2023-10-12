# SAP DateTime

[![License: MIT][license-mit]](LICENSE)
[![Maintainability][maintainability-badge]][maintainability]
[![Test Coverage][coverage-badge]][coverage]

Extends PHP's [DateTime][datetime] class by SAP week, date, time and timestamp
format. Extends PHP's [DateInterval][dateinterval] class by SAP time format.

* SAP week in format `<year><week>`
* SAP date in format `<year><month><day>`
* SAP time in format `<hour><minute><second>`
* SAP timestamp in format `<year><month><day><hour><minute><second>`

## Usage

```
composer require php-sap/datetime:~1.0.0
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
 * Output: 13-20-01
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
$time = new SapDateInterval('PTH21M45S05');
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

[license-mit]: https://img.shields.io/badge/license-MIT-blue.svg
[maintainability-badge]: https://api.codeclimate.com/v1/badges/1bfab925e39bfaf242fc/maintainability
[maintainability]: https://codeclimate.com/github/php-sap/datetime/maintainability
[coverage-badge]: https://api.codeclimate.com/v1/badges/1bfab925e39bfaf242fc/test_coverage
[coverage]: https://codeclimate.com/github/php-sap/datetime/test_coverage
[datetime]: https://www.php.net/manual/en/class.datetime.php
[dateinterval]: https://www.php.net/manual/en/class.dateinterval.php
