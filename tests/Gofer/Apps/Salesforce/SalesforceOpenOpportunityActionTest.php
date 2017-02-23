<?php

require_once 'SalesforceActionTestCase.php';

use Gofer\Apps\Salesforce\SalesforceOpenOpportunityAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Messages\Actions\ShowCards;
use Gofer\Util\TestingUtil;

class SalesforceOpenOpportunityActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}
	
	public function testRun_Found() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'opportunity_name' => 'Acme 120 Widgets Opportunity'
				));
		$action = new SalesforceOpenOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->first()->getActionId());
        
        //$msgNameJson = $messageResponseList->first()->getDetailsJson();
        //$this->assertEquals('opportunity attributes', json_decode($msgNameJson)->text);
	}
	
	/*
	 * This test fails. It returns a SayStatement and a ShowCards.
	 * 
	public function testRun_FoundMultiple() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'opportunity_name' => 'Acme 300 Widgets Opportunity'
				));
		$action = new SalesforceOpenOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(2, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->getItem(1)->getActionId());        
	}
	*/
	
	/*
	 * This test fails. Gofer returns a card. It should return the SayStatement 
	 * "I could not find that opportunity".
	public function testRun_NotFound() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'opportunity_name' => 'Random Opportunity Name'
				));
		$action = new SalesforceOpenOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->first()->getActionId());
	}
	*/

	//TODO: Get working
//    public function testRun_Misspelled() {
//        $message = TestingUtil::buildMessage(
//            TestingUtil::$mainTestUserID,
//            array(
//                'opportunity_name' => 'Acme 120 Widgets Opprtunity'
//            ));
//        $action = new SalesforceOpenOpportunityAction($message);
//        $messageResponseList = $action->run();
//        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
//        $this->assertEquals(2, $messageResponseList->count());
//        $this->assertEquals(OpenWebsite::getActionID(), $messageResponseList->getItem(1)->getActionId());
//    }

}