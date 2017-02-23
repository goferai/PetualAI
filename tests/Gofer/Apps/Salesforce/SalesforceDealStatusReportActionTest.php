<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Apps\Salesforce\SalesforceDealStatusReportAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;

class SalesforceDealStatusReportActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}
	
	public function testRun() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID, 
				array());
		$action = new SalesforceDealStatusReportAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertTrue($messageResponseList->first()->getAction()->hasDetailKey('text'));
		$this->assertNotEquals('There was a problem looking up the deals.', $messageResponseList->first()->getAction()->getValueForKey('text'));
	}
	
}