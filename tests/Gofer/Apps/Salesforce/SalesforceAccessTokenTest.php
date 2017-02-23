<?php

require_once 'SalesforceActionTestCase.php';
// use Gofer\Salesforce\SalesforceConnection;
// use Gofer\Util\TestingUtil;
// use Gofer\SDK\UserApp;
// use Gofer\SDK\User;

class SalesforceAccessTokenTest extends SalesforceAction_TestCase {
	
	public function testRefreshAccessToken() {
		$this->assertEquals(1,1);
		/*
		 * NOTE - you will have to wait for the access token to expire to use this test.
		 * I don't know of a way yet to automatically expire the access token so we can test this.
		 * Easiest way is to go to salesforce account for the user > setup home > session management > change the session timeout to 15 mins and then wait
		 * 
		 * UNCOMMENT THE BELOW TO TEST THE REFRESH TOKEN PROCESS 
		 */
		
// 		$userApp = new UserApp();
// 		$user = new User();
// 		$user->initializeUserForUserID(TestingUtil::$mainTestUserID);
// 		$userApp->initializeForAppID($user, 2);
// 		SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID)->refreshAccessToken();
// 		$userAppAfter = new UserApp();
// 		$userAppAfter->initializeForAppID($user, 2);
// 		$this->assertNotEquals($userApp->access_token, $userAppAfter->access_token); //since we should be getting a new access token
// 		$this->assertEquals($userApp->refresh_token, $userAppAfter->refresh_token);
	}
	
}