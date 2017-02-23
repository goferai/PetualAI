<?php

require_once 'SalesforceActionTestCase.php';

use Gofer\Apps\Salesforce\SalesforceOpenAccountAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Util\TestingUtil;
use Gofer\Messages\Actions\ShowCards;

class SalesforceOpenAccountActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}

    /**
     * @dataProvider runProvider
     * @param $companyName
     */
	public function testRun($companyName) {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'company_name' => $companyName
				));
		$action = new SalesforceOpenAccountAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->first()->getActionId()); 
        //$msgNameJson = $messageResponseList->first()->getDetailsJson();
        //$this->assertEquals('accountDetails', json_decode($msgNameJson)->text);
	}

    public function runProvider() {
        return [
            ['Microsoft Account'],
            ['ocean systems account'],
        ];
    }
	
}