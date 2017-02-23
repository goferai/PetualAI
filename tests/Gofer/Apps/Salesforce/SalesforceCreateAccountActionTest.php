<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Salesforce\SalesforceObjectTypes;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;
use Gofer\Apps\Salesforce\SalesforceCreateAccountAction;

class SalesforceCreateAccountActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
		self::removeTestData();
	}
	
	public function testRun() {
		$message = TestingUtil::buildMessage(
		    TestingUtil::$mainTestUserID,
            ['company_name' => 'Create Account Test']
        );
		$action = new SalesforceCreateAccountAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(2, $messageResponseList->count());
	}
	
	public static function tearDownAfterClass() {
		self::removeTestData();
	}
	
	private static function removeTestData() {
		self::removeTestDataForQuery("SELECT Id from Account where Name = 'Create Account Test'", SalesforceObjectTypes::ACCOUNT);
	}
	
}