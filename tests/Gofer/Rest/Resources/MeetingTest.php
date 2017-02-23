<?php

use Gofer\SDK\Services\MeetingAttendee;
use Gofer\SDK\Services\MeetingAttendeeDate;
use Gofer\Util\DateUtil;
use Gofer\Util\EmailUtil;
use Gofer\Util\Log;
use Gofer\Util\TestingUtil;
use Gofer\Util\WebServiceUtil;

class MeetingTest extends PHPUnit_Framework_TestCase {

	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;
    private static $setupRan = false;
    private static $calendarCreated = false;
    private static $eventID;

    public static function setUpBeforeClass() {
    	if (!self::$setupRan) {
    		self::$setupRan = true;
            EmailUtil::$fakeSendEmailsForTesting = true;
    		TestingUtil::emptyTablesForMeeting();
    		TestingUtil::moveTestDataForMeetingID('7527f811-52dd-47cb-9b38-902ab0db09ec');
    	}
    }

	public function testGetMeetings() {
		$user = TestingUtil::getTestAdminUser();
		$this->expectOutputRegex('/^\[{"meetingId":.*/');
		TestingUtil::mockRouterCall($user, 'get', '/api/meetings?timezone='.DateUtil::TZ_PST);
	}

    public function testGetMeeting() {
    	$user = TestingUtil::getTestAdminUser();
		$this->expectOutputRegex('/^\{"meetingId":.*/');
		TestingUtil::mockRouterCall($user, 'get', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec?timezone='.DateUtil::TZ_PST);
    }

    public function testUpdateMeeting() {
    	$user = TestingUtil::getTestAdminUser();
    	$descriptionTest = "ChangedDescription";
    	$data = '{"meetingId":"7527f811-52dd-47cb-9b38-902ab0db09ec","state":"In Progress","subject":null,"description":"'.$descriptionTest.'","location":null,"start":null,"end":null}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/.*'.$descriptionTest.'.*/');
    	TestingUtil::mockRouterCall($user, 'patch', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec');
    }

    public function testGetMeetingAttendees() {
    	$user = TestingUtil::getTestAdminUser();
    	$this->expectOutputRegex('/^\[{"attendeeId":.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees');
    }

    public function testUpdateMeetingAttendee() {
    	$user = TestingUtil::getTestAdminUser();
    	$data = '{"state":"'. MeetingAttendee::STATE_ON_HOLD.'"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/.*'.MeetingAttendee::STATE_ON_HOLD.'.*/');
    	TestingUtil::mockRouterCall($user, 'patch', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/df508f39-3311-43b6-9aca-75b8aad9f89c');
    }

    public function testGetMeetingAttendeeEmails() {
    	$user = TestingUtil::getTestAdminUser();
    	$this->expectOutputRegex('/^\[{.*"meetingId":.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/df508f39-3311-43b6-9aca-75b8aad9f89c/emails');
    }

    public function testGetMeetingAttendeeDates() {
    	$user = TestingUtil::getTestAdminUser();
    	$this->expectOutputRegex('/^\[{"meetingAttendeeDateId":.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/df508f39-3311-43b6-9aca-75b8aad9f89c/attendeeDates');
    }

    public function testCreateMeetingAttendeeDate() {
    	$user = TestingUtil::getTestAdminUser();
    	$date = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
    	$randomHour = rand(1,23);
    	$date1 = $date->setTime($randomHour, 0);
    	$date2 = DateUtil::getEndDateForDuration($date1, 1799);
    	$date1 = $date1->format(DateUtil::FORMAT_ISO8601);
    	$date2 = $date2->format(DateUtil::FORMAT_ISO8601);
    	$data = '{"title":"Loading","start":"'.$date1.'","end":"'.$date2.'"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/.*'.$date1.'.*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/df508f39-3311-43b6-9aca-75b8aad9f89c/attendeeDates?timezone='.DateUtil::TZ_PST);
    }

    public function testGetMeetingDates() {
    	$user = TestingUtil::getTestAdminUser();
    	$this->expectOutputRegex('/^\[{"meetingAttendeeDateId":.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendeeDates?timezone='.DateUtil::TZ_PST);
    }

    public function testScheduleMeetingDate() {
        EmailUtil::$fakeSendEmailsForTesting = true;
    	$log = new Log(basename(__FILE__));
    	$log->debug("testScheduleMeetingDate");
    	$user = TestingUtil::getTestAdminUser();
        $log->debug("testScheduleMeetingDate - user = ".json_encode($user));
    	self::$eventID = str_replace("-", "", \Ramsey\Uuid\Uuid::uuid4()->toString());
        $log->debug("testScheduleMeetingDate - eventID = ".self::$eventID);
    	$data = '{"eventId":"'.self::$eventID.'","meetingAttendeeDateId":"1d63903e-56db-4387-b3a0-e5172b994bfb","meetingId":"7527f811-52dd-47cb-9b38-902ab0db09ec","start":"2016-03-15 08:00:00","end":"2016-03-15 08:30:00"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/^{"meetingAttendeeDateId":.*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendeeDates/1d63903e-56db-4387-b3a0-e5172b994bfb/schedule');
    	self::$calendarCreated = true;
    }

    public function testGetMeetingBusyDates() {
    	$this->expectOutputRegex('/.*\[{.*"start".*/');
        $minDate = (new DateTime())->modify('-1 day')->format(DateUtil::FORMAT_DATE_YYYY_MM_DD);
        $maxDate = (new DateTime())->modify('+3 days')->format(DateUtil::FORMAT_DATE_YYYY_MM_DD);
    	$url = '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/busyDates?minDate='.$minDate.'T17%3A00%3A00-07%3A00&maxDate='.$maxDate.'T17%3A00%3A00-07%3A00&timezone='.DateUtil::TZ_PST;
    	TestingUtil::mockRouterCall(null, 'get', $url);
    }

    public function testCreateAttendeeDate_Attendee() {
    	$data = '{"start":"2016-03-19T17:00:00-07:00","end":"2016-03-19T17:30:00-07:00","response":"'. MeetingAttendeeDate::RESPONSE_ACCEPTED.'"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/^{"meetingAttendeeDateId":".*/');
    	$url = '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/5561e844-3849-489b-b488-4450d7069bf1/attendeeDates?timezone='.DateUtil::TZ_PST;
    	TestingUtil::mockRouterCall(null, 'post', $url);
    }

    public function testDeleteAttendeeDate_Attendee() {
    	$this->expectOutputRegex('/^{"meetingAttendeeDateId":.*/');
    	$url = '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/5561e844-3849-489b-b488-4450d7069bf1/attendeeDates/2803d831-f2ea-4c88-8af3-c0dfd88a931a';
    	TestingUtil::mockRouterCall(null, 'delete', $url);
    }

    public function testUpdateAttendeeDate_Attendee() {
    	$data = '{"meetingAttendeeDateId":"2803d831-f2ea-4c88-8af3-c0dfd88a931a","meetingId":"7527f811-52dd-47cb-9b38-902ab0db09ec","start":"2016-03-22T16:00:00","end":"2016-03-22T17:00:00","attendeeId":"5561e844-3849-489b-b488-4450d7069bf1","response":"Accepted","deletedFlag":"N"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/^\{"meetingAttendeeDateId":.*/');
    	$url = '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/5561e844-3849-489b-b488-4450d7069bf1/attendeeDates/2803d831-f2ea-4c88-8af3-c0dfd88a931a?timezone='.DateUtil::TZ_PST;
    	TestingUtil::mockRouterCall(null, 'patch', $url);
    }

    public function testUpdateAttendeeDate_Attendee_ErrorNotAllowed() {
    	$data = '{"meetingAttendeeDateId":"2803d831-f2ea-4c88-8af3-c0dfd88a931a","meetingId":"7527f811-52dd-47cb-9b38-902ab0db09ec","start":"2016-03-22T16:00:00","end":"2016-03-22T17:00:00","attendeeId":"5561e844-3849-489b-b488-4450d7069bf1","response":"Accepted","deletedFlag":"N"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/.*"error".*/');
    	$url = '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/5561e844-3849-489b-b488-4450d7069bf1/attendeeDates/1d63903e-56db-4387-b3a0-e5172b994bfb?timezone='.DateUtil::TZ_PST;
    	TestingUtil::mockRouterCall(null, 'patch', $url);
    }

    public function testUpdateAttendeeDate_Attendee_ErrorIDMismatch() {
    	$data = '{"meetingAttendeeDateId":"WrongID","meetingId":"7527f811-52dd-47cb-9b38-902ab0db09ec","start":"2016-03-22T16:00:00","end":"2016-03-22T17:00:00","attendeeId":"5561e844-3849-489b-b488-4450d7069bf1","response":"Accepted","deletedFlag":"N","local_timezone":"America/Los_Angeles"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/.*"errorCode".*/');
    	$url = '/api/meetings/7527f811-52dd-47cb-9b38-902ab0db09ec/attendees/5561e844-3849-489b-b488-4450d7069bf1/attendeeDates/2803d831-f2ea-4c88-8af3-c0dfd88a931a?timezone='.DateUtil::TZ_PST;
    	TestingUtil::mockRouterCall(null, 'patch', $url);
    }

    public static function tearDownAfterClass() {
    	$log = new Log(basename(__FILE__));
    	if (self::$calendarCreated) {
    		$log->debug("try delete calendar");
            $host = TestingUtil::getTestAdminUser();
            $calendar = (new \Gofer\SDK\Services\UserService())->getDefaultCalendar($host);
    		$calendar->unschedule(self::$eventID);
    		self::$calendarCreated = false;
    	}
    }

}