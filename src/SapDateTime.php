<?php

/**
 * File src/SapDateTime.php
 *
 * SAP week, date, time and timestamp conversions.
 *
 * @package datetime
 * @author  Gregor J.
 * @license MIT
 */

declare(strict_types=1);

namespace phpsap\DateTime;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class phpsap\DateTime\SapDateTime
 *
 * Helps to convert between PHPs DateTime objects and SAP week, date, time and
 * timestamp.
 *
 * @package phpsap\DateTime
 * @author  Gregor J.
 * @license MIT
 */
class SapDateTime extends DateTime
{
    /**
     * @const string SAP week format.
     */
    public const SAP_WEEK = 'oW';

    /**
     * @const string SAP date format.
     */
    public const SAP_DATE = 'Ymd';

    /**
     * @const string SAP time format
     */
    public const SAP_TIME = 'His';

    /**
     * @const string SAP timestamp format.
     */
    public const SAP_TIMESTAMP = 'YmdHis';

    /**
     * This regular expression covers only years from 1900 to 9999.
     * @var string regular expression for parsing an SAP week.
     */
    protected static string $sapWeekRegex = '~^(19[\d]{2}|[2-9][\d]{3})(0[1-9]|[1-4][\d]|5[0-3])$~';

    /**
     * Parse an SAP week string into a new DateTime object.
     *
     * @param  string  $sapWeek  String representing the SAP week.
     * @param  DateTimeZone|null  $timezone A DateTimeZone object representing the desired
     *                                time zone.
     * @return DateTime|false
     * @throws Exception
     */
    public static function createFromSapWeek(string $sapWeek, DateTimeZone $timezone = null): DateTime|false
    {
        if (preg_match(static::$sapWeekRegex, $sapWeek, $matches) !== 1) {
            return false;
        }
        $week = sprintf('%sW%s', $matches[1], $matches[2]);
        return new parent($week, $timezone);
    }

    /**
     * Parse a string into a new DateTime object according to the specified format.
     *
     * @param string        $format   Format accepted by date().
     * @param string        $datetime     String representing the time.
     * @param  DateTimeZone|null  $timezone A DateTimeZone object representing the desired
     *                                time zone.
     * @return DateTime|false
     * @throws Exception
     *
     * @link https://php.net/manual/en/datetime.createfromformat.php
     */
    public static function createFromFormat(
        string $format,
        string $datetime,
        DateTimeZone|null $timezone = null
    ): DateTime|false {
        if ($format === static::SAP_WEEK) {
            return static::createFromSapWeek($datetime, $timezone);
        }
        if ($format === static::SAP_DATE) {
            $result = parent::createFromFormat($format, $datetime, $timezone);
            if ($result !== false) {
                $result->setTime(0, 0);
            }
            return $result;
        }
        return parent::createFromFormat($format, $datetime, $timezone);
    }
}
