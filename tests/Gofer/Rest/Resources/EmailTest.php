<?php

use Gofer\Util\EmailUtil;
use \Gofer\Util\TestingUtil;

class EmailTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;
    private static $setupRan;
	
    public static function setUpBeforeClass() {
    	if (!self::$setupRan) {
    		self::$setupRan = true;
    
    		TestingUtil::emptyTables(array(
    				'srvc_emails',
    				'srvc_meeting_emails',
    				'srvc_meetings'
    		));
    
    		TestingUtil::moveTestData('test_meetings', 'srvc_meetings', array('meeting_id'=>'1527f811-52dd-47cb-9b38-902ab0db0911'));
    		TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>array(
    				'1f31eef0-d8c8-4643-b646-cb5076907411', 
    				'2c31eef0-d8c8-4643-b646-cb5076907422',
    				'3b31eef0-d8c8-4643-b646-cd5076907953'
    		)));
    		TestingUtil::moveTestData('test_meeting_emails', 'srvc_meeting_emails', array('email_id'=>'1f31eef0-d8c8-4643-b646-cb5076907411'));
    	}
    }
    
    public function testGetEmails() {
    	$user = TestingUtil::getTestAdminUser();
		$this->expectOutputRegex('/^\[{.*"emailId":.*/');
		TestingUtil::mockRouterCall($user, 'get', '/api/emails');
    }
    
    public function testGetEmail() {
    	$user = TestingUtil::getTestAdminUser();
    	$this->expectOutputRegex('/^\{.*"emailId":.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/emails/1f31eef0-d8c8-4643-b646-cb5076907411');
    }
    
    public function testSendEmail() {
        EmailUtil::$fakeSendEmailsForTesting = true;
    	$user = TestingUtil::getTestAdminUser();
    	$data = '{"meetingId":"1527f811-52dd-47cb-9b38-902ab0db0911", "state":"Completed","emailFrom":"gofer2@devmail.gofer.co","emailTo":"chipallen22@yahoo.com","emailSubject":"testSendEmail","emailText":"test"}';
    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/^{.*"emailId".*testSendEmail.*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/emails');
    }
    
    public function testCreateMeetingFromEmail() {
    	$user = TestingUtil::getTestAdminUser();
    	$data = '{"state":"Completed","emailFrom":"gofer2@devmail.gofer.co","emailTo":"chipallen22@yahoo.com","emailSubject":"Test","emailText":"Test"}';
    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/^{"meetingId":.*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/emails/2c31eef0-d8c8-4643-b646-cb5076907422/createMeeting');
    }
    
    public function testCreateMeetingFromEmail_2() {
        $emailId = '22be9e60-1358-4cc9-9078-0d36df9ec2a1';
    	$user = TestingUtil::getTestAdminUser();
    	TestingUtil::emptyTablesForMeeting();
    	TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>$emailId));
    	TestingUtil::moveTestData('test_email_info', 'srvc_email_info', array('email_id'=>$emailId));
    	$data = '{"state":"In Progress","email_id":"22be9e60-1358-4cc9-9078-0d36df9ec2a1"}';
    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $data;
    	$this->expectOutputRegex('/^{"meetingId":.*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/emails/'.$emailId.'/createMeeting');
    	$row = TestingUtil::getSingleRow("SELECT count(g.attendee_id) as cnt FROM gofer.srvc_meeting_attendees g
			join gofer.srvc_meeting_emails e on g.meeting_id = e.meeting_id
			where e.email_id = '$emailId'");
    	$this->assertEquals('2', $row->cnt);
    }
    
    
//TODO: P1 - Get working again
//    public function testAssignEmailToMeeting() {
//    	EmailUtil::$fakeSendEmailsForTesting = true;
//    	$user = TestingUtil::getTestAdminUser();
//    	$data = '{"meetingId":"1527f811-52dd-47cb-9b38-902ab0db0911"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $data;
//    	$this->expectOutputRegex('/^{"meetingId":.*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/emails/3b31eef0-d8c8-4643-b646-cd5076907953/assignToMeeting');
//    }
    
    public function testCreateMeetingFromEmail_EnsureDurationAndLocation() {
    	EmailUtil::$fakeSendEmailsForTesting = true;
    	$user = TestingUtil::getTestAdminUser();
    	TestingUtil::emptyTables(array('srvc_emails', 'srvc_email_info'));
    	TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id'=>'7e590847-a132-4e0e-ae3e-803d4880cbed'));
    	$this->expectOutputRegex('/^{"meetingId":.*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/emails/7e590847-a132-4e0e-ae3e-803d4880cbed/createMeeting');
    	$row = TestingUtil::getSingleRow("SELECT a.*
													FROM gofer.srvc_emails g
													join gofer.srvc_meeting_emails e on g.email_id = e.email_id
													join gofer.srvc_meetings a on e.meeting_id = a.meeting_id
													where g.email_id = '7e590847-a132-4e0e-ae3e-803d4880cbed'");
    	usleep(100000); //pause a 1/10th of a second to make sure data is there
    	$this->assertNotFalse($row);
    	$this->assertNotNull($row->location);
    	$this->assertNotNull($row->duration_minutes);
    }
    
}