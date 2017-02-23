<?php

require_once 'SalesforceActionTestCase.php';
use Gofer\Apps\Salesforce\SalesforceOpportunityPriorityListAction;
use Gofer\Messages\Actions\OpenWebsite;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;

class SalesforceOpportunityPriorityListActionTest extends SalesforceAction_TestCase {
	
	public function testRun_Found() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array( ));
		$action = new SalesforceOpportunityPriorityListAction($message);

        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(OpenWebsite::getActionId(), $messageResponseList->first()->getActionId());
	}
	
}