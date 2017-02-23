<?php

use Gofer\SDK\Services\MeetingAttendeeList;
use Gofer\Util\EmailUtil;
use Gofer\Util\TestingUtil;
use Gofer\Workers\MeetingNoResponseWorker;

class MeetingNoResponseWorkerTest extends PHPUnit_Framework_TestCase {
	
	public function testRun_Attempt1() {
		EmailUtil::$fakeSendEmailsForTesting = true;
		
		TestingUtil::emptyTables(array(
				'srvc_emails',
				'srvc_meeting_attendees',
				'srvc_meeting_attendee_dates',
				'srvc_meetings',
				'srvc_email_info',
				'srvc_meeting_emails',
				'srvc_meeting_attendee_emails',
		));
		TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>array(
				'e72512b3-eed7-11e5-a5a1-02ee00ea8287',
				'e71c89d6-eed7-11e5-a5a1-02ee00ea8287'
		)));
		TestingUtil::moveTestData('test_meeting_attendees', 'srvc_meeting_attendees', array('meeting_id'=>'ff71b4c3-eed7-11e5-a5a1-02ee00ea8287'));
		TestingUtil::moveTestData('test_meeting_attendee_dates', 'srvc_meeting_attendee_dates', array('meeting_id'=>'ff71b4c3-eed7-11e5-a5a1-02ee00ea8287'));
		TestingUtil::moveTestData('test_meetings', 'srvc_meetings', array('meeting_id'=>'ff71b4c3-eed7-11e5-a5a1-02ee00ea8287'));
		TestingUtil::moveTestData('test_email_info', 'srvc_email_info', array('email_id'=>array(
				'e72512b3-eed7-11e5-a5a1-02ee00ea8287',
				'e71c89d6-eed7-11e5-a5a1-02ee00ea8287'
		)));
		TestingUtil::moveTestData('test_meeting_emails', 'srvc_meeting_emails', array('meeting_id'=>'ff71b4c3-eed7-11e5-a5a1-02ee00ea8287'));
		TestingUtil::moveTestData('test_meeting_attendee_emails', 'srvc_meeting_attendee_emails', array('email_id'=>array(
				'e72512b3-eed7-11e5-a5a1-02ee00ea8287',
				'e71c89d6-eed7-11e5-a5a1-02ee00ea8287'
		)));
		TestingUtil::updateTestData(
				'test_meeting_attendees', 
				array('attendee_id'=>'bc21c7be-eed8-11e5-a5a1-02ee00ea8287'), 
				array('last_email_to_attendee_date'=>'2016-03-16T12:00:00')
		);
		
		$worker = new MeetingNoResponseWorker();
		$results = $worker->run();
		$this->assertInstanceOf(MeetingAttendeeList::class, $results);
		$this->assertEquals(1, $results->count());
		$this->assertEquals(1, $results->first()->getRetryAttempts());
	}
	
	public function testRun_Attempt2() {
		EmailUtil::$fakeSendEmailsForTesting = true;
	
		TestingUtil::emptyTables(array(
				'srvc_emails',
				'srvc_meeting_attendees',
				'srvc_meeting_attendee_dates',
				'srvc_meetings',
				'srvc_email_info',
				'srvc_meeting_emails',
				'srvc_meeting_attendee_emails',
		));
		$meetingId = '48422393-ef76-11e5-a5a1-02ee00ea8287';
		$emails = array(
				'73d416d7-ef77-11e5-a5a1-02ee00ea8287',
				'73d67e16-ef77-11e5-a5a1-02ee00ea8287'
		);
		TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>$emails));
		TestingUtil::moveTestData('test_meeting_attendees', 'srvc_meeting_attendees', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meeting_attendee_dates', 'srvc_meeting_attendee_dates', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meetings', 'srvc_meetings', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_email_info', 'srvc_email_info', array('email_id'=>$emails));
		TestingUtil::moveTestData('test_meeting_emails', 'srvc_meeting_emails', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meeting_attendee_emails', 'srvc_meeting_attendee_emails', array('email_id'=>$emails));
		TestingUtil::updateTestData(
				'srvc_meeting_attendees',
				array('attendee_id'=>'ffd8e11e-ef78-11e5-a5a1-02ee00ea8287'),
				array('last_email_to_attendee_date'=>'2016-03-09T12:00:00', 'last_retry_attempt_date'=>'2016-03-12T12:00:00', 'retry_attempts'=>1)
				);
	
		$worker = new MeetingNoResponseWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingAttendeeList::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertEquals(2, $results->first()->getRetryAttempts());
	}
	
	public function testRun_Attempt3() {
		EmailUtil::$fakeSendEmailsForTesting = true;
	
		TestingUtil::emptyTables(array(
				'srvc_emails',
				'srvc_meeting_attendees',
				'srvc_meeting_attendee_dates',
				'srvc_meetings',
				'srvc_email_info',
				'srvc_meeting_emails',
				'srvc_meeting_attendee_emails',
		));
		$meetingId = '48422393-ef76-11e5-a5a1-02ee00ea8287';
		$emails = array(
				'73d416d7-ef77-11e5-a5a1-02ee00ea8287',
				'73d67e16-ef77-11e5-a5a1-02ee00ea8287'
		);
		TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>$emails));
		TestingUtil::moveTestData('test_meeting_attendees', 'srvc_meeting_attendees', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meeting_attendee_dates', 'srvc_meeting_attendee_dates', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meetings', 'srvc_meetings', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_email_info', 'srvc_email_info', array('email_id'=>$emails));
		TestingUtil::moveTestData('test_meeting_emails', 'srvc_meeting_emails', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meeting_attendee_emails', 'srvc_meeting_attendee_emails', array('email_id'=>$emails));
		TestingUtil::updateTestData(
				'srvc_meeting_attendees',
				array('attendee_id'=>'ffd8e11e-ef78-11e5-a5a1-02ee00ea8287'),
				array('last_email_to_attendee_date'=>'2016-03-09T12:00:00', 'last_retry_attempt_date'=>'2016-03-12T12:00:00', 'retry_attempts'=>2)
				);
	
		$worker = new MeetingNoResponseWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingAttendeeList::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertEquals(3, $results->first()->getRetryAttempts());
	}
	
	public function testRun_AttemptOverLimit() {
		EmailUtil::$fakeSendEmailsForTesting = true;
		TestingUtil::emptyTables(array(
				'srvc_emails',
				'srvc_meeting_attendees',
				'srvc_meeting_attendee_dates',
				'srvc_meetings',
				'srvc_email_info',
				'srvc_meeting_emails',
				'srvc_meeting_attendee_emails',
		));
		$meetingId = '48422393-ef76-11e5-a5a1-02ee00ea8287';
		$emails = array(
				'73d416d7-ef77-11e5-a5a1-02ee00ea8287',
				'73d67e16-ef77-11e5-a5a1-02ee00ea8287'
		);
		TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>$emails));
		TestingUtil::moveTestData('test_meeting_attendees', 'srvc_meeting_attendees', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meeting_attendee_dates', 'srvc_meeting_attendee_dates', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meetings', 'srvc_meetings', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_email_info', 'srvc_email_info', array('email_id'=>$emails));
		TestingUtil::moveTestData('test_meeting_emails', 'srvc_meeting_emails', array('meeting_id'=>$meetingId));
		TestingUtil::moveTestData('test_meeting_attendee_emails', 'srvc_meeting_attendee_emails', array('email_id'=>$emails));
		TestingUtil::updateTestData(
				'srvc_meeting_attendees',
				array('attendee_id'=>'ffd8e11e-ef78-11e5-a5a1-02ee00ea8287'),
				array('last_email_to_attendee_date'=>'2016-03-09T12:00:00', 'last_retry_attempt_date'=>'2016-03-12T12:00:00', 'retry_attempts'=>3)
				);
	
		$worker = new MeetingNoResponseWorker();
		$results = $worker->run();
        $this->assertInstanceOf(MeetingAttendeeList::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertEquals(3, $results->first()->getRetryAttempts());
	}
	
}