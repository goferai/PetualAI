<?php

use Gofer\Util\TestingUtil;
use Gofer\Util\WebServiceUtil;

class MessageTest extends PHPUnit_Framework_TestCase {

	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;

    public function test_skypeMessage() {
		$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
		TestingUtil::$fakeSendSkypeMessages = true;
		$inputObject = '{
          "type": "message",
          "id": "17LJMKkzKrljwuLQ",
          "timestamp": "2016-09-18T18:50:01.063Z",
          "serviceUrl": "https://skype.botframework.com",
          "channelId": "skype",
          "from": {
            "id": "29:1ELHNhhD1oD0NHBUb6i5Ugqskp1_d25_XD3aQ2QXd9R4",
            "name": "Chip Allen"
          },
          "conversation": {
            "id": "29:1ELHNhhD1oD0NHBUb6i5Ugqskp1_d25_XD3aQ2QXd9R4"
          },
          "recipient": {
            "id": "28:314b8f2e-f6dc-46bd-b160-a250892f97ec",
            "name": "Gofer Dev"
          },
          "text": "open the dickenson account",
          "entities": []
        }';
		WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
    	$this->expectOutputString(''); //nothing is returned to skype except a 200 response
    	TestingUtil::mockRouterCall($user, 'post', '/api/messages/skype');
    }

    public function test_skypeMessage_defaultIntent() {
        $user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
        TestingUtil::$fakeSendSkypeMessages = true;
        $inputObject = '{
          "type": "message",
          "id": "17LJMKkzKrljwuLQ",
          "timestamp": "2016-09-18T18:50:01.063Z",
          "serviceUrl": "https://skype.botframework.com",
          "channelId": "skype",
          "from": {
            "id": "29:1ELHNhhD1oD0NHBUb6i5Ugqskp1_d25_XD3aQ2QXd9R4",
            "name": "Chip Allen"
          },
          "conversation": {
            "id": "29:1ELHNhhD1oD0NHBUb6i5Ugqskp1_d25_XD3aQ2QXd9R4"
          },
          "recipient": {
            "id": "28:314b8f2e-f6dc-46bd-b160-a250892f97ec",
            "name": "Gofer Dev"
          },
          "text": "take a screenshot",
          "entities": []
        }';
        WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
        $this->expectOutputString(''); //nothing is returned to skype except a 200 response
        TestingUtil::mockRouterCall($user, 'post', '/api/messages/skype');
    }

//	public function testPostMessage_Gofer_TakeScreenshot() {
//		$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//		$inputObject = '{"text":"take a screenshot"}';
//		\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Take Screenshot".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Gofer_OpenApplication() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"open textpad"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Statement".*"text":"Opened".*"action_id":"Open Application".*"application":"textpad".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Gofer_CloseApplication() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"close putty"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Statement".*"text":"Closed".*"action_id":"Close Application".*"application":"putty".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Gofer_OpenWebsite() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"open salesforce.com"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Open Website".*"url":"http.*salesforce\.com".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Gofer_ThingsICanSay() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"what things can I say?"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Open Website".*"url":"http.*things-i-can-say".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Gofer_TypeText() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"write this down"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Statement".*"text":"What do you want to write\?".*"action_id":"Type Text".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_ErrorEmpty() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":""}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Error".*"text":"There was a problem looking up that message.".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_ErrorMissingText() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Error".*"text":"There was a problem looking up that message.".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Salesforce_OpenDashboard() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"open the sales manager dashboard"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Statement".*"text":"Dashboard opened".*"action_id":"Open Website".*"url":"http.*salesforce\.com.{1,2}\w{8,}".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//
//    public function testPostMessage_Salesforce_OpenReport() {
//    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
//    	$inputObject = '{"text":"open the sales manager pipeline report"}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"action_id":"Say Statement".*"text":"Opened".*"action_id":"Open Website".*"url":"http.*salesforce\.com.{1,2}\w{8,}".*/');
//    	TestingUtil::mockRouterCall($user, 'post', '/api/messages');
//    }
//

    /**
     * @dataProvider testPostMessage_Salesforce_SearchNearbyProvider
     * @param $user
     * @param $jsonBody
     * @param $expectedOutputRegex
     */
    public function testPostMessage_Salesforce_SearchNearby($user, $jsonBody, $expectedOutputRegex) {
        WebServiceUtil::$testingSimulatedRequestBody = $jsonBody;
        $this->expectOutputRegex($expectedOutputRegex);
        TestingUtil::mockRouterCall($user, 'post', '/api/messages');
    }

    public function testPostMessage_Salesforce_SearchNearbyProvider() {
        $user = TestingUtil::getTestUser();
        return [
            //No latitude and longitude (happens with gofer mobile android)
            [
                $user,
                '{"context":{"latitude":"null","longitude":"null"},"sourceId":4,"text":"find accounts near me"}',
                '/.*I could not determine your location.*/i'
            ],
            //Valid latitude and longitude (from gofer mobile android)
            [
                $user,
                '{"context":{"latitude":"33.158885","longitude":"-117.252585"},"sourceId":4,"text":"find accounts near me"}',
                '/.*Found.*account.*cards.*/'
            ]
        ];
    }

}