<?php

/**
 * File src/SapDateInterval.php
 *
 * SAP time to interval conversions for times > 24h.
 *
 * @package datetime
 * @author  Gregor J.
 * @license MIT
 */

declare(strict_types=1);

namespace phpsap\DateTime;

use DateInterval;
use Exception;

/**
 * Class SapDateInterval
 *
 * Helps to convert SAP time values to intervals for times greater than 24 hours.
 *
 * @package phpsap\DateTime
 * @author  Gregor J.
 * @license MIT
 */
class SapDateInterval extends DateInterval
{
    /**
     * @var string SAP time format for DateInterval
     */
    public const SAP_TIME = '%H%I%S';

    /**
     * Sets up a DateInterval from the relative parts of the string
     * @param string $datetime
     * @return DateInterval
     * @throws Exception
     * @link https://php.net/manual/en/dateinterval.createfromdatestring.php
     */
    public static function createFromDateString($datetime): DateInterval
    {
        $matches = [];
        if (preg_match('~^([\d]{2})([0-5][\d])([0-5][\d])$~', $datetime, $matches)) {
            return new parent(sprintf(
                'PT%sH%sM%sS',
                $matches[1],
                $matches[2],
                $matches[3]
            ));
        }
        return parent::createFromDateString($datetime);
    }
}
