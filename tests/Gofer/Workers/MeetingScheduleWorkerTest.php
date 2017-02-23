<?php

use Gofer\SDK\Services\Meeting;
use Gofer\SDK\Services\MeetingList;
use Gofer\SDK\Services\MeetingService;
use Gofer\SDK\Services\UserPreference;
use Gofer\SDK\Services\UserPreferenceService;
use Gofer\Util\DateUtil;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Util\TestingUtil;
use Gofer\Salesforce\Salesforce;
use Gofer\Util\EmailUtil;
use Gofer\Salesforce\Objects\Event;

class MeetingScheduleWorkerTest extends PHPUnit_Framework_TestCase {
	
	public function testRun_Schedule1() {
	    $meetingId = '52322393-ef76-11e5-a5a1-02ee00ea8932';
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
		
		TestingUtil::emptyTablesForMeeting();
		TestingUtil::moveTestDataForMeetingID($meetingId);
        $originalDate = new \DateTime('2016-03-19T00:00:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

		$worker = new \Gofer\Workers\MeetingScheduleWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $results);
		$this->assertEquals(1, $results->count());
	}
	
	public function testRun_ScheduleWith2Guests() {
        $meetingId = '6007cb63-7e4c-4b73-a37b-cbd98f7a6027';
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
		TestingUtil::emptyTablesForMeeting();
		TestingUtil::moveTestDataForMeetingID($meetingId);

        $originalDate = new \DateTime('2016-04-17T18:44:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

		$worker = new \Gofer\Workers\MeetingScheduleWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $results);
        $this->assertEquals(1, $results->count());

        $meetingService = new MeetingService();
		$meeting = $meetingService->get((new \Gofer\SDK\Services\MeetingServiceOptions())->setMeetingId($meetingId));
		$this->assertEquals(Meeting::STATE_SCHEDULED, $meeting->getState());
	}
	
	public function testRun_ScheduleSpecificTimeWithNoGuests() {
	    $meetingId = '0d063620-8990-45bc-ab37-5459680e1789';
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
		TestingUtil::emptyTablesForMeeting();
		TestingUtil::moveTestDataForMeetingID($meetingId);

        $originalDate = new \DateTime('2016-06-01T19:41:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

		$worker = new \Gofer\Workers\MeetingScheduleWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $results);
        $this->assertEquals(1, $results->count());

        $meetingService = new MeetingService();
        $meeting = $meetingService->get((new \Gofer\SDK\Services\MeetingServiceOptions())->setMeetingId($meetingId));
		$this->assertEquals(Meeting::STATE_SCHEDULED, $meeting->getState());
	}
	
	public function testRun_ExpectCorrectScheduleTime() {
	    $meetingId = 'e1a6b785-7b1c-41bf-96f9-4591335598b4';
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
		TestingUtil::emptyTablesForMeeting();
		TestingUtil::moveTestDataForMeetingID($meetingId);

        $originalDate = new \DateTime('2016-04-18T13:30:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

		$worker = new \Gofer\Workers\MeetingScheduleWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $results);
        $this->assertEquals(1, $results->count());

        $meetingService = new MeetingService();
        $meeting = $meetingService->get((new \Gofer\SDK\Services\MeetingServiceOptions())->setMeetingId($meetingId));
		$date = DateUtil::convertStringToTimezone($meeting->getStart(), DateUtil::TZ_UTC, $meeting->getTimezone());
        $hr = ($date->format('I') === '1') ? '10' : '09' ; //adjust one hour higher during daylight savings
        $expectedDate = new \DateTime("2016-04-21 $hr:15:00", new \DateTimeZone($meeting->getTimezone()));
        $expectedDate->modify('+'.$daysToAdd.' days');
		$this->assertEquals($expectedDate->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S), $date->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S));
	}	
	
	public function testRun_TimeHostHasOpenButDidNotAccept() {
		//This should schedule it since they are open even though they did not accept
        $meetingId = 'f6ed8139-2f5d-4b27-8461-b71fb4fe5b01';
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
		TestingUtil::emptyTablesForMeeting();
		TestingUtil::moveTestDataForMeetingID($meetingId);

        $originalDate = new \DateTime('2016-04-19T17:15:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

		$worker = new \Gofer\Workers\MeetingScheduleWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $results);
        $this->assertEquals(1, $results->count());

        $meetingService = new MeetingService();
        $meeting = $meetingService->get((new \Gofer\SDK\Services\MeetingServiceOptions())->setMeetingId($meetingId));
		$this->assertNotNull($meeting->getStart());
		$date = DateUtil::convertStringToTimezone($meeting->getStart(), DateUtil::TZ_UTC, $meeting->getTimezone());
        $hr = ($date->format('I') === '1') ? '11' : '10' ; //adjust one hour higher during daylight savings
        $expectedDate = new \DateTime("2016-04-26 $hr:30:00", new \DateTimeZone($meeting->getTimezone()));
        $expectedDate->modify('+'.$daysToAdd.' days');
		$this->assertEquals($expectedDate->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S), $date->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S));
	}
	
	public function testRun_ScheduleAndAddEventToSalesforceOpportunity() {
		//This should schedule it and add it to salesforce
        $meetingId = '2046b13d-68ff-4e6b-b475-d09a514cd7bb';
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = false;
		TestingUtil::emptyTablesForMeeting();
		TestingUtil::moveTestDataForMeetingID($meetingId);

        $originalDate = new \DateTime('2016-04-27T13:17:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

		$worker = new \Gofer\Workers\MeetingScheduleWorker();
		$meetingList = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $meetingList);
        $this->assertEquals(1, $meetingList->count());

        $meetingService = new MeetingService();
        $meeting = $meetingService->get((new \Gofer\SDK\Services\MeetingServiceOptions())->setMeetingId($meetingId));
		$this->assertNotNull($meeting->getStart());
		$date = DateUtil::convertStringToTimezone($meeting->getStart(), DateUtil::TZ_UTC, $meeting->getTimezone());
        $hr = ($date->format('I') === '1') ? '14' : '13' ; //adjust one hour higher during daylight savings
        $expectedDate = new \DateTime("2016-05-02 $hr:30:00", new \DateTimeZone($meeting->getTimezone()));
        $expectedDate->modify('+'.$daysToAdd.' days');
		$this->assertEquals( $expectedDate->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S), $date->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S));
		
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
		$salesforce = new Salesforce();
		$startDate = new \DateTime($meeting->getStart());
		$results = $salesforce->query("select id, WhatId, WhoId, Location, Description from event where Subject = '".$meeting->getSubject()."' and StartDateTime = ".$startDate->format(DateUtil::FORMAT_ISO8601));
		$this->assertGreaterThanOrEqual(1, count($results));
		$this->assertEquals($meeting->getDescription(), $results[0]->Description);
		$this->assertEquals('0063600000613ALAAY', $results[0]->WhatId);
		$this->assertEquals($meeting->getLocation(), $results[0]->Location);
		$this->assertEquals('0033600000BvT8tAAF', $results[0]->WhoId);
		
		//Cleanup
		if (count($results) >= 1) {
            foreach($results as $result) {
                $event = new Event();
                $event->initializeForID($result->Id);
                $event->delete();
            }
		}
	}
	
	public function testRun_ScheduleOutlookCalendar() {
	    $meetingId = '52322393-ef76-11e5-a5a1-02ee00ea8932';
        $user = TestingUtil::getTestAdminUser();
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::$fakeCalendarSchedulingFlag = true;
		TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
        TestingUtil::emptyTablesForMeeting();
        TestingUtil::moveTestDataForMeetingID($meetingId);

        $originalDate = new \DateTime('2016-03-19T00:00:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');
        TestingUtil::updateDatesForMeetingId($meetingId, $daysToAdd*24);

        $userPreferenceBuilder = new \Gofer\SDK\Services\UserPreferenceBuilder();
		$userPreference = $userPreferenceBuilder
            ->setUserId($user->getUserId())
            ->setPreferenceKey(UserPreference::PREF_DEFAULT_CALENDAR_APP)
            ->setPreferenceValue('7')
            ->build();
		$userPreferenceService = new UserPreferenceService();
        $userPreferenceService->upsert($userPreference);
		
		$worker = new \Gofer\Workers\MeetingScheduleWorker();
        $meetingList = $worker->run();
        $this->assertInstanceOf(MeetingList::class, $meetingList);
		$this->assertCount(1, $meetingList);

        $userPreference = $userPreferenceBuilder
            ->setUserId($user->getUserId())
            ->setPreferenceKey(UserPreference::PREF_DEFAULT_CALENDAR_APP)
            ->setPreferenceValue('4')
            ->build();
        $userPreferenceService = new UserPreferenceService();
        $userPreferenceService->upsert($userPreference);
	}
}