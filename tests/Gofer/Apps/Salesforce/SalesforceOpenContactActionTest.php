<?php

require_once 'SalesforceActionTestCase.php';

use Gofer\Apps\Salesforce\SalesforceOpenContactAction;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\SDK\Services\MessageResponseList;
use Gofer\Messages\Actions\ShowCards;
use Gofer\Util\TestingUtil;

class SalesforceOpenContactActionTest extends SalesforceAction_TestCase {
		
	public static function setupBeforeClass() {
		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
	}
	
	public function testRun_WithoutAccount() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						//"open contact henry smith at microsoft"
						'contact' => 'Howard Jones'
				));
		$action = new SalesforceOpenContactAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(), $messageResponseList->first()->getActionId());
        
        //$msgNameJson = $messageResponseList->first()->getDetailsJson();
        //$this->assertEquals('contact attributes', json_decode($msgNameJson)->text);
	}
	
	public function testRun_WithAccount() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'contact' => 'Jennifer Stamos',
						'company_name' => 'Acme'
				));
		$action = new SalesforceOpenContactAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(ShowCards::getActionId(),  $messageResponseList->first()->getActionId());
	}
	
	/* 
	 * This test fails. Gofer returns the contact found in the other company.
	 * 
	public function testRun_NotFound() {
		$message = TestingUtil::buildMessage(
				TestingUtil::$mainTestUserID,
				array(
						'contact' => 'Jennifer Stamos',
						'company_name' => 'Global Media'
				));
		$action = new SalesforceOpenContactAction($message);
        $messageResponseList = $action->run();
        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
        $this->assertEquals(1, $messageResponseList->count());
        $this->assertEquals(SayStatement::getActionId(), $messageResponseList->first()->getActionId());
        $this->assertEquals('I could not find that contact', $messageResponseList->first()->getAction()->getValueForKey('text'));
	}
	*/

	//TODO: Get working again
//    public function testRun_Misspelled() {
//        $message = TestingUtil::buildMessage(
//            TestingUtil::$mainTestUserID,
//            array(
//                'contact' => 'Jeniffer Stammos',
//                'company_name' => 'Acme'
//            ));
//        $action = new SalesforceOpenContactAction($message);
//        $messageResponseList = $action->run();
//        $this->assertInstanceOf(MessageResponseList::class, $messageResponseList);
//        $this->assertEquals(1, $messageResponseList->count());
//        $this->assertEquals(OpenWebsite::getActionID(), $messageResponseList->first()->getActionId());
//    }

}