<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Apps\Salesforce\SalesforceUpdateOpportunityAction;
use Gofer\Messages\Actions\SayQuestion;
use Gofer\Messages\Actions\SayStatement;
use Gofer\Messages\Actions\ShowCards;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;

class SalesforceUpdateOpportunityActionTest extends SalesforceAction_TestCase {
	
	public function testRun_Found() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array( 
						'opportunity_name' => 'Acme 120 Widgets Opportunity',
						'opportunity_stage' => 'Qualification'
				));
		$action = new SalesforceUpdateOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(2, $messageResponseList->count());
        $this->assertEquals(SayQuestion::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->getItem(1)->getActionId());
	}
	
	public function testRun_NoFields() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'opportunity_name' => 'Acme 120 Widgets Opportunity'
				));
		$action = new SalesforceUpdateOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals('I could not tell which fields you wanted updated', $messageResponseList->first()->getAction()->getValueForKey('text'));
	}
	
	public function testRun_NotFound() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'opportunity_name' => 'Random Opportunity Name'
				));
		$action = new SalesforceUpdateOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals('I could not find that opportunity', $messageResponseList->first()->getAction()->getValueForKey('text'));
	}
	
	public function testRun_StageNameNotFound() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'opportunity_name' => 'Acme 120 Widgets Opportunity',
						'opportunity_stage' => 'RandomStageName'
				));
		$action = new SalesforceUpdateOpportunityAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals('I could not find that opportunity stage name in the list', $messageResponseList->first()->getAction()->getValueForKey('text'));
	}

	//TODO: Get working again
//	public function testRun_MultipleFound() {
//		$message = TestingUtil::buildMessage(
//				TestingUtil::$mainTestUserID,
//				array(
//						'opportunity_name' => 'Acme 300 Widgets Opportunity'
//				));
//		$action = new SalesforceUpdateOpportunityAction($message);
//        $messageResponseList = $action->run();
//        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
//        $this->assertEquals(2, $messageResponseList->count());
//        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
//        $this->assertEquals('I found multiple. Select which one is the correct one.', $messageResponseList->first()->getAction()->getValueForKey('text'));
//	}
}