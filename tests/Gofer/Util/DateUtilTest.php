<?php

use Gofer\Util\DateUtil;

class DateUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider dtstrings
     * @param $dateString
     * @param $initialTimeZone
     * @param $toTimeZone
     * @param $expected
     */
	public function test_convertStringToTimezone($dateString, $initialTimeZone, $toTimeZone, $expected)
	{
		$result = DateUtil::convertStringToTimezone($dateString, $initialTimeZone, $toTimeZone);
		$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', $expected);
		$this->assertEquals($expectedDate, $result);
	}
	
	public function dtstrings() {
		return [
			['2016-09-06 00:00:00', 'GMT', 'PST', '2016-09-05 16:00:00'],
        ];
	}

    /**
     * @dataProvider dtobjs
     * @param $dateTime
     * @param $expected
     */
	public function test_convertToGMT($dateTime, $expected)
	{
		$result = DateUtil::convertToGMT($dateTime);
		$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', $expected);
		$this->assertEquals($expectedDate, $result);
	}
	
	public function dtobjs() {
		$date1 = new DateTime('2016-09-06 00:00:00', new DateTimeZone('PST'));
		return [
			[$date1, '2016-09-06 08:00:00'],
        ];
	}

    /**
     * @dataProvider timestmps
     * @param $unixTimestamp
     * @param $expected
     */
	public function test_formatDateForMySQL($unixTimestamp, $expected)
	{
		$result = DateUtil::formatDateForMySQL($unixTimestamp);
		$this->assertEquals($expected, $result);
	}
	
	public function timestmps() {
		$date1= new DateTime('2016-09-06 00:00:00', new DateTimeZone('PST'));
		return [
			[$date1->getTimestamp(), '2016-09-06T08:00:00+00:00'],
        ];
	}
	
	public function test_getCurrentDateTimeUTC()
	{
		$expected = new \DateTime('now', new \DateTimeZone('UTC'));
		$result = DateUtil::getCurrentDateTimeUTC();
		$this->assertEquals($expected, $result);
		
		// This test is redundant
	}
	
	public function testGetEndDateForTimeRange() {
    	$startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-26 10:00:00');
    	$endDate = DateUtil::getEndDateForTimeRange($startDate, 'hour');
    	$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-26 10:59:59');
    	$this->assertEquals($expectedDate, $endDate);
    }
    
    public function testGetEndDateForTimeRange_day() {
    	$startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-26 00:00:00');
    	$endDate = DateUtil::getEndDateForTimeRange($startDate, 'day');
    	$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-26 23:59:59');
    	$this->assertEquals($expectedDate, $endDate);
    }
    
    public function testGetEndDateForTimeRange_week() {
    	$startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2016-03-13 00:00:00');
    	$endDate = DateUtil::getEndDateForTimeRange($startDate, 'week');
    	$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', '2016-03-19 23:59:59');
    	$this->assertEquals($expectedDate, $endDate);
    }
    
    public function testGetEndDateForTimeRange_month() {
    	$startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-01 00:00:00');
    	$endDate = DateUtil::getEndDateForTimeRange($startDate, 'month');
    	$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-31 23:59:59');
    	$this->assertEquals($expectedDate, $endDate);
    }
    
    public function testGetEndDateForTimeRange_year() {
    	$startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-01-01 00:00:00');
    	$endDate = DateUtil::getEndDateForTimeRange($startDate, 'year');
    	$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-31 23:59:59');
    	$this->assertEquals($expectedDate, $endDate);
    }

    /**
     * @dataProvider datetms
     * @param $startDate
     * @param $durationSeconds
     * @param $expected
     */
	public function test_getEndDateForDuration($startDate, $durationSeconds, $expected)
	{
		$result = DateUtil::getEndDateForDuration($startDate, $durationSeconds);
		$expectedDate = DateTime::createFromFormat('Y-m-d H:i:s', $expected);
		$this->assertEquals($expectedDate, $result);
	}
	
	public function datetms() {
		return [
			[new DateTime('2016-09-05 00:00:00', new DateTimeZone('UTC')), '120', '2016-09-05 00:02:00'],
			[new DateTime('2016-09-05 00:00:00', new DateTimeZone('UTC')), '-120', '2016-09-04 23:58:00'],
        ];
	}

    /**
     * @dataProvider zoneNames
     * @param $timezone
     * @param $expected
     */
    public function test_getNameForTimeZone($timezone, $expected) 
	{
		$result = DateUtil::getNameForTimeZone($timezone);
        $this->assertEquals($expected, $result);
    }
	
	public function zoneNames() {
		return [
			['America/Los_Angeles', 'Pacific Time'],
			['America/Denver', 'Mountain Time'],
			['America/Anchorage', 'Alaska Time'],
			['America/Phoenix', 'Mountain Time - Arizona'],
			['America/Chicago', 'Central Time'],
			['America/New_York', 'Eastern Time'],
			['Pacific/Honolulu', 'Hawaii Time'],
			['America/', ''],
			['', ''],
        ];
	}

    /**
     * @dataProvider dblDatetms
     * @param $startDate
     * @param $endDate
     * @param bool $absolute
     * @param $expected
     */
	public function test_getSecondsBetweenTwoDates($startDate, $endDate, $absolute = true, $expected)
	{
		$result = DateUtil::getSecondsBetweenTwoDates($startDate, $endDate, $absolute);
		$this->assertEquals($expected, $result);
	}
	
	public function dblDatetms() {
		return [
			[new DateTime('2016-09-05 00:00:00', new DateTimeZone('UTC')), new DateTime('2016-09-05 00:02:30', new DateTimeZone('UTC')), TRUE, 150],
			[new DateTime('2016-09-05 00:02:30', new DateTimeZone('UTC')), new DateTime('2016-09-05 00:00:00', new DateTimeZone('UTC')), TRUE, 150],
			[new DateTime('2016-09-05 00:00:00', new DateTimeZone('UTC')), new DateTime('2016-09-05 00:02:30', new DateTimeZone('UTC')), FALSE, -150],
        ];
	}

    /**
     * @dataProvider dateInPastProvider
     * @param $dateTime
     * @param $expected
     */
    public function test_isDateInPast($dateTime, $expected)
    {
        $result = DateUtil::isDateInPast($dateTime);
        $this->assertEquals($expected, $result);
    }

    public function dateInPastProvider() {
        return [
            [(new DateTime('now', new DateTimeZone('UTC')))->modify('+10 minute'), false],
            [(new DateTime('now', new DateTimeZone('UTC')))->modify('-10 minute'), TRUE],
        ];
    }

    /**
     * @dataProvider dateInFutureProvider
     * @param $dateTime
     * @param $expected
     */
    public function test_isDateInFuture($dateTime, $expected)
    {
        $result = DateUtil::isDateInFuture($dateTime);
        $this->assertEquals($expected, $result);
    }

    public function dateInFutureProvider() {
        return [
            [(new DateTime('now', new DateTimeZone('UTC')))->modify('+10 minute'), true],
            [(new DateTime('now', new DateTimeZone('UTC')))->modify('-10 minute'), false],
        ];
    }

}