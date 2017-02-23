<?php

use Gofer\SDK\Services\Email;
use Gofer\Util\TestingUtil;
use Gofer\Util\EmailUtil;
use Gofer\Workers\EmailNewWorker;

/**
 * Breaking out a 2nd class cause it was failing for some reason on circleCI but not on localhost when it was all in one file.
 */
class EmailNewWorkerTest2 extends PHPUnit_Framework_TestCase {

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
                '2eb23004-e631-11e6-b64f-06aeeb5a3d03',
                'deaa71cf-4277-4406-a0e3-8d1f1b503a4c',
            ]]
        );
    }

    public function test_oneHourDuration() {
        $emailId = '2eb23004-e631-11e6-b64f-06aeeb5a3d03';
        $originalDate = new \DateTime('2016-11-16T23:23:52', new \DateTimeZone('America/Los_Angeles'));
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

    /**
     * This failed in production
     */
    public function test_requestSpecificMeetingWithNoGuests() {
        $emailId = 'deaa71cf-4277-4406-a0e3-8d1f1b503a4c';
        $originalDate = new \DateTime('2017-02-10T06:50:52', new \DateTimeZone('America/Los_Angeles'));
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

    /**
     * How does it run with no emails
     * !!!!!!!!!!!!!!
     * Run this one last
     * !!!!!!!!!!!!!!
     */
    public function testRunEmpty() {
        TestingUtil::emptyTables(array('srvc_emails', 'srvc_email_info'));
        $emailNewWorker = new EmailNewWorker();
        $results = $emailNewWorker->setTestEmailId('fake')->run();
        $this->assertInternalType('array', $results);
        $this->assertCount(0, $results);
        $this->assertEquals(0, $emailNewWorker->getMessageList()->count());
    }

}