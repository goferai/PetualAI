<?php

use Gofer\Calendar\Calendars\GoogleCalendar;
use Gofer\Calendar\Event;
use Gofer\SDK\Services\UserCalendarBuilder;
use Gofer\SDK\Services\UserCalendarService;
use Gofer\Util\TestingUtil;
use Gofer\Util\DateUtil;

class GoogleCalendarTest extends PHPUnit_Framework_TestCase {
	
    public function testGetAllUserCalendars() {
    	$host = TestingUtil::getTestAdminUser();
    	$googleCalendar = new GoogleCalendar($host);
    	$calendars = $googleCalendar->getAllUserCalendars();
    	$this->assertGreaterThan(1, count($calendars));
    }
    
    public function testGetFreeBusyDates() {
    	$host = TestingUtil::getTestAdminUser();
    	$googleCalendar = new GoogleCalendar($host);
    	$minDate = new DateTime('2017-02-01');
        /** @var DateTime $maxDate */
    	$maxDate = clone $minDate;
    	$maxDate = $maxDate->modify('+7 days');
    	$freeBusyDates = $googleCalendar->getFreeBusyDates($minDate->format(DateUtil::FORMAT_ISO8601), $maxDate->format(DateUtil::FORMAT_ISO8601));
    	$this->assertGreaterThan(1, count($freeBusyDates));
    }
    
    public function testGetFreeBusyDates_MultipleCalendars() {
    	$host = TestingUtil::getTestAdminUser();
    	//Make sure we have a 2nd calendar
        $userCalendarBuilder = new UserCalendarBuilder();
        $userCalendar = $userCalendarBuilder
            ->setUserId($host->getUserId())
            ->setAppId(4)
            ->setCalendarId('sarahallen2@gmail.com')
            ->build();
        $userCalendarService = new UserCalendarService();
        $userCalendarService->upsert($userCalendar);
    	
    	$googleCalendar = new GoogleCalendar($host);
    	$minDate = new DateTime('2016-05-01');
        /** @var DateTime $maxDate */
    	$maxDate = clone $minDate;
    	$maxDate = $maxDate->modify('+2 days');
    	$freeBusyDates = $googleCalendar->getFreeBusyDates($minDate->format(DateUtil::FORMAT_ISO8601), $maxDate->format(DateUtil::FORMAT_ISO8601));
    	$this->assertGreaterThan(1, count($freeBusyDates));
    }


    public function test_ScheduleEvent() {
        $host = TestingUtil::getTestAdminUser();
        $calendar = new GoogleCalendar($host);

        $event = new Event();
        $event->generateNewEventID();
        $event->summary = 'Test Event';
        $event->description = 'Test Event';
        $event->location = 'Test Event';
        $event->startDate = (new \DateTime())->format(DateUtil::FORMAT_ISO8601);
        $event->endDate = (new \DateTime())->modify('+30 minutes')->format(DateUtil::FORMAT_ISO8601);
        //$event->attendees = $attendees->toArray();
        $eventCreated = $calendar->schedule($event);
        $this->assertNotNull($eventCreated);
        $calendar->unschedule($event->id);
    }
    
}