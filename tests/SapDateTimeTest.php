<?php

/**
 * File tests/SapDateTimeTest.php
 *
 * Test SapDateTime class.
 *
 * @package datetime
 * @author  Gregor J.
 * @license MIT
 */

declare(strict_types=1);

namespace tests\phpsap\DateTime;

use DateTime;
use DateTimeZone;
use Exception;
use phpsap\DateTime\SapDateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class tests\phpsap\DateTime\SapDateTimeTest
 *
 * Unit tests for the SapDateTime class.
 *
 * @package tests\phpsap\DateTime
 * @author  Gregor J.
 * @license MIT
 */
class SapDateTimeTest extends TestCase
{
    /**
     * Data provider for valid SAP week strings and the expected output.
     *
     * @return array<int, array<int, string>>
     */
    public static function validSapWeeks(): array
    {
        return [
            ['201846', '2018 week 46', '2018-11-12 00:00:00'],
            ['190801', '1908 week 01', '1907-12-30 00:00:00'],
            ['190853', '1908 week 53', '1908-12-28 00:00:00'],
            ['190952', '1909 week 52', '1909-12-27 00:00:00'],
            ['191052', '1910 week 52', '1910-12-26 00:00:00'],
            ['191152', '1911 week 52', '1911-12-25 00:00:00'],
            ['191301', '1913 week 01', '1912-12-30 00:00:00']
        ];
    }

    /**
     * Test valid SAP week conversions.
     *
     * @param  string  $sapWeek  The SAP week string.
     * @param  string  $expected The expected week in format <year>W<week>.
     * @param  string  $timestamp The expected timestamp in format Y-m-d H:i:s
     * @dataProvider validSapWeeks
     * @throws Exception
     */
    public function testParseSapWeeks(string $sapWeek, string $expected, string $timestamp): void
    {
        $dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_WEEK, $sapWeek);
        static::assertInstanceOf(DateTime::class, $dateTime);
        static::assertSame($expected, $dateTime->format('o \w\e\ek W'));
        static::assertSame($timestamp, $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * Data provider for invalid SAP week strings.
     *
     * @return array<int, array<int, string>>
     */
    public static function invalidSapWeeks(): array
    {
        return [
            ['189952'],
            ['33333'],
            ['19901'],
            ['201854']
        ];
    }

    /**
     * Test valid SAP week conversions.
     *
     * @param  string  $sapWeek  The SAP week string.
     * @dataProvider invalidSapWeeks
     * @throws Exception
     */
    public function testParseInvalidSapWeeks(string $sapWeek): void
    {
        $dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_WEEK, $sapWeek);
        static::assertFalse($dateTime);
    }

    /**
     * Data provider of timestamps and their according SAP week strings.
     * @return array<int, array<int, string>>
     */
    public static function timestampsAndSapWeeks(): array
    {
        return [
            ['2018-10-19 08:09:10', '201842'],
            ['1907-12-31 09:10:11', '190801'],
            ['1908-12-31 10:11:12', '190853'],
            ['1909-12-31 11:12:13', '190952'],
            ['1910-12-31 12:13:14', '191052'],
            ['1911-12-31 13:14:15', '191152'],
            ['1912-12-31 14:15:16', '191301']
        ];
    }

    /**
     * Test formatting timestamps to SAP week strings.
     * @param  string  $timestamp Timestamp string
     * @param  string  $expected SAP week string
     * @throws Exception
     * @dataProvider timestampsAndSapWeeks
     */
    public function testCreateSapWeeks(string $timestamp, string $expected): void
    {
        $dateTime = new SapDateTime($timestamp);
        $sapWeekString = $dateTime->format(SapDateTime::SAP_WEEK);
        static::assertSame($expected, $sapWeekString);
    }

    /**
     * Data provider of SAP dates and their ISO date representations.
     * @return array<int, array<int, string>>
     */
    public static function sapDatesAndIsoDates(): array
    {
        return [
            ['20181101', '2018-11-01 00:00:00'],
            ['19071231', '1907-12-31 00:00:00'],
            ['19080101', '1908-01-01 00:00:00'],
            ['19091201', '1909-12-01 00:00:00'],
            ['19100110', '1910-01-10 00:00:00'],
            ['19110601', '1911-06-01 00:00:00'],
            ['19120229', '1912-02-29 00:00:00']
        ];
    }

    /**
     * Test parsing SAP dates.
     * @param  string  $sapDate
     * @param  string  $isoDate
     * @throws Exception
     * @dataProvider sapDatesAndIsoDates
     */
    public function testParseSapDates(string $sapDate, string $isoDate): void
    {
        $dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_DATE, $sapDate);
        static::assertInstanceOf(DateTime::class, $dateTime);
        static::assertSame($isoDate, $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * Data provider of times and their according SAP time strings.
     * @return array<int, array<int, string>>
     */
    public static function timesAndSapTimes(): array
    {
        return [
            ['08:10:31', '081031'],
            ['21:12:00', '211200'],
            ['12:01:01', '120101'],
            ['13:20:45', '132045'],
        ];
    }

    /**
     * Test formatting an ISO time as SAP time.
     * @param  string  $isoTime
     * @param  string  $sapTime
     * @throws Exception
     * @dataProvider timesAndSapTimes
     */
    public function testCreateSapTimes(string $isoTime, string $sapTime): void
    {
        $dateTime = new SapDateTime($isoTime);
        static::assertSame($sapTime, $dateTime->format(SapDateTime::SAP_TIME));
    }

    /**
     * Test reading time from SAP and formatting it as ISO.
     * @param  string  $isoTime
     * @param  string  $sapTime
     * @throws Exception
     * @dataProvider timesAndSapTimes
     */
    public function testParseSapTimes(string $isoTime, string $sapTime): void
    {
        $dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_TIME, $sapTime);
        static::assertInstanceOf(DateTime::class, $dateTime);
        static::assertSame($isoTime, $dateTime->format('H:i:s'));
    }

    /**
     * Data provider of timestamps and their according SAP dates.
     * @return array<int, array<int, string>>
     */
    public static function timestampsAndSapDates(): array
    {
        return [
            ['2018-12-21 08:09:10', '20181221'],
            ['1907-12-31 09:10:11', '19071231'],
            ['1908-01-01 10:11:12', '19080101'],
            ['1909-12-01 11:12:13', '19091201'],
            ['1910-01-10 12:13:14', '19100110'],
            ['1911-06-01 13:14:15', '19110601'],
            ['1912-02-29 14:15:16', '19120229'],
        ];
    }

    /**
     * Test formatting DateTime objects as SAP dates.
     * @param  string  $timestamp
     * @param  string  $sapDate
     * @throws Exception
     * @dataProvider timestampsAndSapDates
     */
    public function testCreateSapDates(string $timestamp, string $sapDate): void
    {
        $dateTime = new SapDateTime($timestamp);
        static::assertSame($sapDate, $dateTime->format(SapDateTime::SAP_DATE));
    }

    /**
     * Data provider of timestamps and their according SAP timestamps.
     * @return array<int, array<int, string>>
     */
    public static function timestampsAndSapTimestamps(): array
    {
        return [
            ['2018-10-19 08:09:10', '20181019080910'],
            ['1907-12-31 09:10:11', '19071231091011'],
            ['1908-12-31 10:11:12', '19081231101112'],
            ['1909-12-31 11:12:13', '19091231111213'],
            ['1910-12-31 12:13:14', '19101231121314'],
            ['1911-06-01 13:14:15', '19110601131415'],
            ['1912-02-29 14:15:16', '19120229141516']
        ];
    }

    /**
     * Test parsing SAP timestamps.
     * @param  string  $isotime
     * @param  string  $saptime
     * @throws Exception
     * @dataProvider timestampsAndSapTimestamps
     */
    public function testParseSapTimestamps(string $isotime, string $saptime): void
    {
        $dateTime = SapDateTime::createFromFormat(SapDateTime::SAP_TIMESTAMP, $saptime);
        static::assertInstanceOf(DateTime::class, $dateTime);
        static::assertSame($isotime, $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test formatting DateTime objects as SAP timestamps.
     * @param  string  $isotime
     * @param  string  $saptime
     * @throws Exception
     * @dataProvider timestampsAndSapTimestamps
     */
    public function testCreateSapTimestamps(string $isotime, string $saptime): void
    {
        $dateTime = new SapDateTime($isotime);
        static::assertSame($saptime, $dateTime->format(SapDateTime::SAP_TIMESTAMP));
    }

    /**
     * Data provider for timezones.
     * @return array<int, array<int, mixed>>
     */
    public static function provideTimezones(): array
    {
        return [
            [
                SapDateTime::SAP_TIMESTAMP,
                '20200301000102',
                new DateTimeZone('CET'),
                '2020-03-01T00:01:02+01:00',
                '2020-02-29T23:01:02+00:00'
            ],
            [
                'Y-m-d H:i:s',
                '2020-03-01 00:01:02',
                new DateTimeZone('CET'),
                '2020-03-01T00:01:02+01:00',
                '2020-02-29T23:01:02+00:00'
            ],
            [
                SapDateTime::SAP_DATE,
                '20200301',
                new DateTimeZone('CET'),
                '2020-03-01T00:00:00+01:00',
                '2020-02-29T23:00:00+00:00'
            ]
        ];
    }

    /**
     * Test timezones.
     * @dataProvider provideTimezones
     * @param  string  $format
     * @param  string  $time
     * @param  DateTimeZone  $zone
     * @param  string  $expected
     * @param  string  $utc
     * @throws Exception
     */
    public function testTimezones(string $format, string $time, DateTimeZone $zone, string $expected, string $utc): void
    {
        $dateTime = SapDateTime::createFromFormat($format, $time, $zone);
        static::assertInstanceOf(DateTime::class, $dateTime);
        static::assertSame($expected, $dateTime->format('c'));
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        static::assertSame($utc, $dateTime->format('c'));
    }
}
