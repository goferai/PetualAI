<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Apps\Salesforce\SalesforceAddTaskAction;
use Gofer\Messages\Actions\SayQuestion;
use Gofer\Messages\Actions\ShowCards;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Salesforce\SalesforceObjectTypes;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class SalesforceAddTaskActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
		self::removeTestData();
	}
	
	public function testRun() {
		$now = new \DateTime();
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'message_subject' => 'testRun', 
						'company_name' => 'Acme', 
						'datetime' => $now->format(DateUtil::FORMAT_ISO8601)
				));
		$action = new SalesforceAddTaskAction($message);
		$messageResponseList = $action->run();
		$this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
		$this->assertEquals(2, $messageResponseList->count());
        $this->assertEquals(SayQuestion::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->getItem(1)->getActionId());
	}
	
	public static function tearDownAfterClass() {
		self::removeTestData();
	}
	
	private static function removeTestData() {
		self::removeTestDataForQuery("SELECT Id from TASK where WhatId = '0013600000E6n8EAAR' and Subject = 'testRun'", SalesforceObjectTypes::TASK);
	}
	
}