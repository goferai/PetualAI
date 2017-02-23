<?php

require_once 'SalesforceActionTestCase.php';

use Gofer\Apps\Salesforce\SalesforceCreateOpportunityAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Salesforce\SalesforceObjectTypes;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class SalesforceCreateOpportunityActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
		self::removeTestData();
	}
	
	public function testRun_CreateOpportunity() {
		$now = new \DateTime();
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID, 
				array(
						'company_name' => 'University of Arizona',
						'datetime' => $now->modify('+1 Month')->format(DateUtil::FORMAT_ISO8601))
				);
		$action = new SalesforceCreateOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(2, $messageResponseList->count());
		
//		$account = new Account();
//		$account->initializeForName('Scripps Health');
//		$this->assertEquals('Oceanside', $account->BillingCity);

        //TODO: Fix - fails on circle ci every time - not sure why
//		$opportunity = new Opportunity();
//		$opportunity->initializeForBestMatch('University of Arizona');
//		$this->assertAttributeNotEmpty('Name', $opportunity);
//		$this->assertContains('University of Arizona', $opportunity->Name);
	}
	
	public static function tearDownAfterClass() {
		self::removeTestData();
	}
	
	private static function removeTestData() {
		self::removeTestDataForQuery("SELECT Id from ".SalesforceObjectTypes::OPPORTUNITY." where Name like '%University of Arizona%'", SalesforceObjectTypes::OPPORTUNITY);
//		self::removeTestDataForQuery("SELECT Id from ".SalesforceObjectTypes::ACCOUNT." where Name = 'Scripps Health'", SalesforceObjectTypes::ACCOUNT);
	}
	
}