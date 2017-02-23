<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Apps\Salesforce\SalesforceOpenDashboardAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Messages\Actions\OpenWebsite;
use Gofer\Messages\Actions\SayStatement;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;

class SalesforceOpenDashboardActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}
	
	public function testRun_Found() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'dashboard' => 'Sales Manager Dashboard'
				));
		$action = new SalesforceOpenDashboardAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(OpenWebsite::getActionId(), $messageResponseList->first()->getActionId());
	}
	
    public function testRun_NotFound() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'dashboard' => 'Random Dashboard Name'
				));
		$action = new SalesforceOpenDashboardAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
	}

	// TODO: Get working again with misspellings. Wit.AI is changing our words from what the user said... this is not matching that.
//    public function testRun_Found_Misspelled() {
//        $message = TestingUtil::buildMessage(
//            TestingUtil::$mainTestUserID,
//            array(
//                'dashboard' => 'Sales Maniger Dashboard'
//            ));
//        $action = new SalesforceOpenDashboardAction($message);
//        $messageResponseList = $action->run();
//        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
//        $this->assertGreaterThanOrEqual(1, $messageResponseList->count());
//        $this->assertEquals(OpenWebsite::getActionId(), $messageResponseList->first()->getActionId());
//    }
	
}