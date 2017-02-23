<?php

use Gofer\SDK\Services\Email;
use Gofer\SDK\Services\EmailInfo;
use Gofer\SDK\Services\MeetingAttendee;
use Gofer\Util\TestingUtil;
use Gofer\Util\EmailUtil;
use Gofer\Workers\EmailNewWorker;

class EmailNewWorkerTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        EmailUtil::$fakeSendEmailsForTesting = true;
        TestingUtil::$fakeCalendarSchedulingFlag = true;
        TestingUtil::$fakeCalendarAddSalesforceEventFlag = true;
        TestingUtil::emptyTables(['srvc_emails', 'srvc_email_info', 'srvc_meeting_emails']);
        TestingUtil::emptyTablesForMeeting();
        TestingUtil::moveTestData(
            'test_emails',
            'srvc_emails',
            ['email_id' => [
                '093c322c-357b-449f-a099-02d5475da1ea',
                '0c10871a-acdb-4991-966d-cb48f6776383',
                '0c784184-bf26-4271-8f77-b7d97380df2b',
                '0d60de14-d75b-4af2-bd53-bac94b0f9430',
                '0f31eef0-d8c8-4643-b646-cb5066907465',
                '82670133-d4ac-4d1a-ba3d-8295507129be',
                'a0ee3dbd-e7af-41f5-85d7-a1cc58ce1393',
                '17e32018-cbe6-4cdc-9c7d-7d531637c059',
                'bc163dea-30e4-4b5d-856f-bbf9fd54263c',
                '22ae9e60-1358-4cc9-9078-0d36df9ec2f1',
                '9886fbd2-5a99-4466-bf46-d346b9c3aad8',
                '2745f5c8-43cb-409d-b960-3c265b8580fb',
                'b9588493-69e8-4484-8927-aa4959f7e510',
                '3df9dae5-64f7-4e49-8764-7862753f5793',
                '62d0646a-5dfb-4946-84c1-1abe3636270a',
                'b9a5b3c5-2433-4857-bc12-1c0c12d72726',
                'cffd73d9-7d65-4309-a357-a8647e24bd10'
            ]]
        );
    }

    public function testTrashEmail1() {
        $emailId = '093c322c-357b-449f-a099-02d5475da1ea';
        $emailNewWorker = new EmailNewWorker();
        $results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
        $this->assertEquals(Email::TYPE_UNKNOWN, $results[0]->getType());
        $this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());

        //make sure the actual data changed
        $email = (new \Gofer\SDK\Services\EmailService())->get((new \Gofer\SDK\Services\EmailServiceOptions())->setEmailId($emailId));
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals(Email::TYPE_UNKNOWN, $email->getType());
        $this->assertEquals(Email::STATE_COMPLETED, $email->getState());
    }
    
    public function testSpamEmail() {
        $emailId = '0c10871a-acdb-4991-966d-cb48f6776383';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(0, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::TYPE_SPAM, $results[0]->getType());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    }
    
    public function testTrashEmailWithoutFrom() {
        $emailId = '0c784184-bf26-4271-8f77-b7d97380df2b';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(0, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::TYPE_TRASH, $results[0]->getType());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    }
    
    public function testMeetingRequestWithoutToContacts() {
        $emailId = '0d60de14-d75b-4af2-bd53-bac94b0f9430';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::TYPE_UNKNOWN, $results[0]->getType());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    }
    
    public function testNewMeetingRequestBasicEmail() {
    	$emailId = '0f31eef0-d8c8-4643-b646-cb5066907465';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    }
    
    public function testNewMeetingRequest_MessageWithoutNamingGofer() {
    	$emailId = '82670133-d4ac-4d1a-ba3d-8295507129be';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	
    	$row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
    	$this->assertNotEquals(false, $row);
    	$this->assertEquals(1, $row->cnt);
    }
    
    public function testNewMeetingRequest_1() {
        $emailId = 'a0ee3dbd-e7af-41f5-85d7-a1cc58ce1393';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	 
    	$row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
    	$this->assertNotEquals(false, $row);
    	$this->assertEquals(1, $row->cnt);
    }
    
    /**
     * Send from your main email to yourself at your alternate email address (people do this when testing)
     */
    public function testSendToAlternateEmail() {
        $emailId = '17e32018-cbe6-4cdc-9c7d-7d531637c059';
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	
    	$row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
    	$this->assertNotEquals(false, $row);
    	$this->assertEquals(1, $row->cnt);
    }
    
    /**
     * Email from Eddie that had troubles
     */
    public function testRun_EddieEmailTest1() {
        $emailId = 'bc163dea-30e4-4b5d-856f-bbf9fd54263c';
    	TestingUtil::$fakeCurrentDateTime = new \DateTime('2016-04-29T09:50:00', new \DateTimeZone('America/Los_Angeles'));
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	 
    	$row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
    	$this->assertNotEquals(false, $row);
    	$this->assertEquals(1, $row->cnt);
    }
    
    /**
     * Email from Eddie that had troubles
     */
    public function testRun_TextTooLongForWit() {
        $emailId = '22ae9e60-1358-4cc9-9078-0d36df9ec2f1';
        TestingUtil::$fakeCurrentDateTime = new \DateTime('2016-05-04T19:06:00', new \DateTimeZone('America/Los_Angeles'));
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::TYPE_UNKNOWN, $results[0]->getType());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    }
    
    public function test_FirstFakeTestEmail() {
        $emailId = '9886fbd2-5a99-4466-bf46-d346b9c3aad8';
        $originalDate = new \DateTime('2016-05-23T13:34:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');

    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	 
    	$row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
    	$this->assertNotEquals(false, $row);
    	$this->assertEquals(1, $row->cnt);
    }
    
    public function test_SpecificTimeNoGuests() {
        $emailId = '2745f5c8-43cb-409d-b960-3c265b8580fb';
    	TestingUtil::$fakeCurrentDateTime = new \DateTime('2016-06-01T19:30:00', new \DateTimeZone('America/Los_Angeles'));
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	
    	$row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
    	$this->assertNotEquals(false, $row);
    	$this->assertEquals(1, $row->cnt);
    }

    public function test_SpecificTime_Thursday1pm() {
        $emailId = 'b9588493-69e8-4484-8927-aa4959f7e510';
        TestingUtil::$fakeCurrentDateTime = new \DateTime('2016-06-01T19:30:00', new \DateTimeZone('America/Los_Angeles'));
        $emailNewWorker = new EmailNewWorker();
        $results = $emailNewWorker->setTestEmailId($emailId)->run();
        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
        $this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());

        $row = TestingUtil::getSingleRow("
                SELECT count(*) as cnt
                FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
                where g.email_id = '$emailId'");
        $this->assertNotEquals(false, $row);
        $this->assertEquals(1, $row->cnt);
    }
    
    public function test_BothGuestsAreUsers() {
        $emailId = '3df9dae5-64f7-4e49-8764-7862753f5793';
    	TestingUtil::$fakeCurrentDateTime = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
    	$emailNewWorker = new EmailNewWorker();
    	$results = $emailNewWorker->setTestEmailId($emailId)->run();
    	$this->assertInternalType('array', $results);
    	$this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
    	
    	//should be 1 - a wit response and an nlp tagger
    	$rows = TestingUtil::getMultipleRows("SELECT * FROM gofer.srvc_email_info where email_id = '$emailId'");
    	$this->assertNotEquals(false, $rows);
    	$this->assertCount(1, $rows);
        $this->assertEquals(EmailInfo::INFOKEY_NLP_DATE_TAGGER_RESPONSE, $rows[0]->info_key);

        //should be 1 - host should be "auto pick" - other user starts out as 'Awaiting Response'... once they respond Yes then gofer moves them to auto pick.
        $rows = TestingUtil::getMultipleRows("SELECT * FROM gofer.srvc_meeting_attendees a join gofer.srvc_meeting_emails e on a.meeting_id = e.meeting_id and e.email_id = '$emailId' and a.state = '". MeetingAttendee::STATE_AUTO_PICK."'");
        $this->assertNotEquals(false, $rows);
        $this->assertCount(1, $rows);
    }

    public function test_relationEdgeFailedEmail() {
        $emailId = '62d0646a-5dfb-4946-84c1-1abe3636270a';
        $originalDate = new \DateTime('2017-01-05T12:34:00', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');

        $emailNewWorker = new EmailNewWorker();
        $results = $emailNewWorker->setTestEmailId($emailId)->run();
        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
        $this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());

        //should be 1 - an nlp tagger
        $rows = TestingUtil::getMultipleRows("SELECT * FROM gofer.srvc_email_info where email_id = '$emailId'");
        $this->assertNotEquals(false, $rows);
        $this->assertCount(1, $rows);
        $this->assertEquals(EmailInfo::INFOKEY_NLP_DATE_TAGGER_RESPONSE, $rows[0]->info_key);

        //should be 1 - host should be "auto pick" - other user starts out as 'Awaiting Response'... once they respond Yes then gofer moves them to auto pick.
        $rows = TestingUtil::getMultipleRows("
            SELECT * 
            FROM gofer.srvc_meeting_attendees a 
            join gofer.srvc_meeting_emails e 
                on a.meeting_id = e.meeting_id 
                and e.email_id = '$emailId' 
                and a.state = '". MeetingAttendee::STATE_AUTO_PICK."'");
        $this->assertNotEquals(false, $rows);
        $this->assertCount(1, $rows);
    }

    /**
     * This email failed in production because the user did not have an access token for their default calendar app but it was set to active state.
     * Proper output is an email back to the user telling them about the problem and the email set to state = completed with NO meeting created.
     */
    public function test_meetingRequestWhenUserAppSettingsBroken() {
        $emailId = 'b9a5b3c5-2433-4857-bc12-1c0c12d72726';
        $originalDate = new \DateTime('2017-02-10T00:49:52', new \DateTimeZone('America/Los_Angeles'));
        $daysToAdd = TestingUtil::calculateDaysToAddToDates($originalDate);
        TestingUtil::$fakeCurrentDateTime = $originalDate->modify('+'.$daysToAdd.' days');

        //Change the user app to mimick what their data looked like with an active user app but a null token
        TestingUtil::executeSQL('insert into gofer.srvc_user_apps 
                                SELECT
                                    user_id,
                                    -1,
                                    access_token,
                                    refresh_token,
                                    instance_url,
                                    app_defined_user_id,
                                    organization_id,
                                    scopes,
                                    last_connected_date,
                                    state,
                                    state_last_changed_date
                                FROM gofer.srvc_user_apps where app_id = 4 and user_id = '.TestingUtil::$mainTestAdminUserID.' on duplicate key update access_token = values(access_token), refresh_token = values(refresh_token)');
        TestingUtil::executeSQL('update gofer.srvc_user_apps set access_token = null, refresh_token = null where app_id = 4 and user_id = '.TestingUtil::$mainTestAdminUserID);

        $emailNewWorker = new EmailNewWorker();
        $results = $emailNewWorker->setTestEmailId($emailId)->run();

        //Put it back before we do any testing of the results
        TestingUtil::executeSQL('insert into gofer.srvc_user_apps 
                                SELECT
                                    user_id,
                                    4,
                                    access_token,
                                    refresh_token,
                                    instance_url,
                                    app_defined_user_id,
                                    organization_id,
                                    scopes,
                                    last_connected_date,
                                    state,
                                    state_last_changed_date
                                FROM gofer.srvc_user_apps where app_id = -1 and user_id = '.TestingUtil::$mainTestAdminUserID.' on duplicate key update access_token = values(access_token), refresh_token = values(refresh_token)');
        TestingUtil::executeSQL('delete from gofer.srvc_user_apps where app_id = -1 and user_id = '.TestingUtil::$mainTestAdminUserID);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
        $this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());

        $row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g
    			where g.email_id = '$emailId' and g.state = '".Email::STATE_COMPLETED."'");
        $this->assertNotEquals(false, $row);
        $this->assertEquals(1, $row->cnt);

        //should be no related meeting
        $rows = TestingUtil::getMultipleRows(" SELECT * FROM gofer.srvc_meeting_emails e where e.email_id = '$emailId'");
        $this->assertFalse($rows);
    }

    /**
     * A gofer user replies "Yes" when asked if they want Gofer to setup the meeting.
     */
    public function test_GoferUserRepliesYes() {
        $emailId = 'cffd73d9-7d65-4309-a357-a8647e24bd10';
        $meetingId = 'c76593fa-8808-4063-a77b-e75944cc14d8';
        TestingUtil::moveTestDataForMeetingID($meetingId);

        TestingUtil::$fakeCurrentDateTime = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
        $emailNewWorker = new EmailNewWorker();
        $results = $emailNewWorker->setTestEmailId($emailId)->run();
        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
        $this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());

        //should be linked to the meeting
        $row = TestingUtil::getSingleRow("
    			SELECT count(*) as cnt
    			FROM gofer.srvc_emails g join gofer.srvc_meeting_emails e on g.email_id = e.email_id
    			where g.email_id = '$emailId'");
        $this->assertNotEquals(false, $row);
        $this->assertEquals(1, $row->cnt);

        //Both attendees should now be autopick
        $rows = TestingUtil::getMultipleRows(
            "SELECT a.* 
            FROM gofer.srvc_meeting_attendees a 
            join gofer.srvc_meeting_emails e on a.meeting_id = e.meeting_id and e.email_id = '$emailId' and a.state = '". MeetingAttendee::STATE_AUTO_PICK."'"
        );
        $this->assertNotEquals(false, $rows);
        $this->assertCount(2, $rows);
    }

//    public function test_TakeScreenshot() {
//    	$emailID = 'd4218d3e-fec0-4a1d-8235-f0deba25e468';
//    	TestingUtil::$fakeCurrentDateTime = new \DateTime('2016-06-09T16:27:00', new \DateTimeZone('America/Los_Angeles'));
//    	$emailNewWorker = new EmailNewWorker();
//    	$results = $emailNewWorker->run();
//    	$this->assertCount(1, $results);
//        $this->assertEquals(1, $emailNewWorker->getMessageList()->count());
//    	$this->assertEquals(Email::STATE_COMPLETED, $results[0]->getState());
//    }

}