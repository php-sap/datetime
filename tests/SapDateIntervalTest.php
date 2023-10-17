<?php

/**
 * File tests/SapDateIntervalTest.php
 *
 * Test SapDateInterval class.
 *
 * @package datetime
 * @author  Gregor J.
 * @license MIT
 */

namespace tests\phpsap\DateTime;

use DateTime;
use Exception;
use phpsap\DateTime\SapDateInterval;
use PHPUnit\Framework\TestCase;

/**
 * Class SapDateIntervalTest
 *
 * Unit tests for the SapDateTime class.
 *
 * @package tests\phpsap\DateTime
 * @author  Gregor J.
 * @license MIT
 */
class SapDateIntervalTest extends TestCase
{
    /**
     * Data provider for valid time return values from SAP.
     * @return array
     */
    public static function provideValidTimes()
    {
        return [
            ['010203', '2020-01-31 00:00:00', '2020-01-31 01:02:03'],
            ['263052', '2020-01-31 00:00:00', '2020-02-01 02:30:52'],
            ['-1 day', '2020-01-31 00:00:00', '2020-01-30 00:00:00'],
        ];
    }

    /**
     * Test converting valid times from SAP to an ISO date and time after 2020-01-31.
     * @param  string  $sapTime
     * @param  string  $startDate
     * @param  string  $expected
     * @throws Exception
     * @dataProvider provideValidTimes
     */
    public function testValidTimes(string $sapTime, string $startDate, string $expected)
    {
        $dateTime = new DateTime($startDate);
        $time = SapDateInterval::createFromDateString($sapTime);
        $dateTime->add($time);
        static::assertSame($expected, $dateTime->format('Y-m-d H:i:s'));
    }
}
