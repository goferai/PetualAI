<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Apps\Salesforce\SalesforceSearchNearbyAccountsAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;

class SalesforceSearchNearbyAccountsActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}
	
	public function test_run() {
		$message = TestingUtil::buildMessage(
		TestingUtil::$mainTestUserID,
		array(
			'location' => 'San Diego'
		));
		$action = new SalesforceSearchNearbyAccountsAction($message);
		$result = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $result);

		$cardsJson = $result->getItem(1)->getDetailsJson();
		$cardsResponse = json_decode($cardsJson);
        $this->assertGreaterThanOrEqual(2, count($cardsResponse->cards));
		$expectedNames = ["Analytics Ventures","Mogl"];
        $this->assertContains($cardsResponse->cards[0]->title, $expectedNames);
	}
	
}
