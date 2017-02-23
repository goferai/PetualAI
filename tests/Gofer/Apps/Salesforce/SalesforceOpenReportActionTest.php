<?php

require_once 'SalesforceActionTestCase.php';

use Gofer\Apps\Salesforce\SalesforceOpenReportAction;
use Gofer\Messages\Actions\ShowCards;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Messages\Actions\SayStatement;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;

class SalesforceOpenReportActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}
	
	public function testRun_Found() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'report' => 'Sales Manager Pipeline Report'
				));
		$action = new SalesforceOpenReportAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(2, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->getItem(1)->getActionId());
	}
	
    public function testRun_NotFound() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'report' => 'Random Report Name'
				));
		$action = new SalesforceOpenReportAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
	}

    public function testRun_Misspelled() {
        $message = TestingUtil::buildMessage(
            TestingUtil::$mainTestUserID,
            array(
                'report' => 'Sales Manger Pipelne Reprt'
            ));
        $action = new SalesforceOpenReportAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(2, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->getItem(1)->getActionId());
    }
	
}