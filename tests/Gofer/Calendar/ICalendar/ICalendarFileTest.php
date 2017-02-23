<?php

use Gofer\Calendar\ICalendar\ICalendarAttendee;
use Gofer\Calendar\ICalendar\ICalendarAttendeeCommonName;
use Gofer\Calendar\ICalendar\ICalendarAttendeeList;
use Gofer\Calendar\ICalendar\ICalendarAttendeeStatus;
use Gofer\Calendar\ICalendar\ICalendarEvent;
use Gofer\Calendar\ICalendar\ICalendarFile;
use Gofer\Calendar\ICalendar\ICalendarOrganizer;

class ICalendarFileTest extends PHPUnit_Framework_TestCase {

    public function test_CalendarFileToString() {
        $organizer = new ICalendarOrganizer(new ICalendarAttendeeCommonName('chip@gofer.co', 'Chip Allen'));
        $event = new ICalendarEvent();
        $event
            ->setCreatedUnixTimestamp(strtotime('2018-08-24 16:12:17'))
            ->setLastModifiedUnixTimestamp(strtotime('2018-08-24 16:12:17'))
            ->setDateStartUnixTimestamp(strtotime('2018-08-24 16:12:17'))
            ->setDateEndUnixTimestamp(strtotime('2018-08-24 16:12:17'))
            ->setDescription('Desc')
            ->setLocation('Loc')
            ->setOrganizer($organizer)
            ->setSummary('Sub')
            ->setUid('6ffe9460-221d-42b3-af1d-a7a633259696');
        $attendeeList = new ICalendarAttendeeList();
        $attendee = new ICalendarAttendee(new ICalendarAttendeeCommonName('chipallen22@yahoo.com', 'John'));
        $attendee->setRsvp(true)->setStatus(ICalendarAttendeeStatus::NEEDS_ACTION);
        $attendeeList->add($attendee);
        $event->setAttendeeList($attendeeList);
        $iCalendarFile = new ICalendarFile();
        $iCalendarFile
            ->setProductId('-//Gofer/Gofer Meeting Scheduling//EN')
            ->setEvent($event);
    	$this->assertGreaterThan(1, strlen($iCalendarFile->toString()));
    }
    
}