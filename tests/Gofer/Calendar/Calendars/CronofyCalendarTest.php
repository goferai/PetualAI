<?php

use Gofer\Calendar\Calendars\Cronofy_OutlookCalendar;
use Gofer\Calendar\Event;
use Gofer\Util\TestingUtil;

class CronofyCalendarTest extends PHPUnit_Framework_TestCase {
	
	public static $eventID;
	public static $calendarID;

    public function test_getAllUserCalendars_Outlook() {
    	$host = TestingUtil::getTestAdminUser();
    	$calendar = new Cronofy_OutlookCalendar($host);
    	$calendars = $calendar->getAllUserCalendars();
    	$this->assertGreaterThan(1, count($calendars));
    }

    public function test_schedule_Outlook() {
    	$host = TestingUtil::getTestAdminUser();
    	$calendar = new Cronofy_OutlookCalendar($host);
    	$event = new Event();
    	$event->generateNewEventID();
    	self::$eventID = $event->id;
    	$event->summary = 'Test Cronofy';
    	$event->description = 'Testing 123';
    	$event->location = 'Test Location';
    	$event->startDate = '2016-06-25T18:00:00Z';
    	$event->endDate = '2016-06-25T18:30:00Z';
    	$event = $calendar->schedule($event);
    	self::$calendarID = $event->calendarID;
    	$this->assertNotNull($event->calendarID);
    }

    public function test_unschedule_Outlook() {
    	$host = TestingUtil::getTestAdminUser();
    	$calendar = new Cronofy_OutlookCalendar($host);
    	$event = new Event();
    	$event->generateNewEventID();
    	$event->summary = 'Test Cronofy';
    	$event->description = 'Testing 123';
    	$event->location = 'Test Location';
    	$event->startDate = '2016-06-25T19:00:00Z';
    	$event->endDate = '2016-06-25T19:30:00Z';
    	$event = $calendar->schedule($event);

    	$calendar->unschedule($event->id, $event->calendarID);
    	$this->assertNotNull($event->calendarID);
    }
    
	public static function tearDownAfterClass() {
		if(isset(self::$eventID)) {
			$host = TestingUtil::getTestAdminUser();
			$calendar = new Cronofy_OutlookCalendar($host);
			$calendar->unschedule(self::$eventID, self::$calendarID);
		}
	}
    
}