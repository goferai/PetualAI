<?php

use Gofer\SDK\Services\UserActivity;
use Gofer\SDK\Services\UserPreference;
use Gofer\Util\TestingUtil;
use Gofer\Util\WebServiceUtil;

class UserTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;
	
	public function testGetUserInfo() {
		$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^{"userId":.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users/userInfo');
    }
    
    public function testGetUserIntentExpressions() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\[{"appName".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users/1/intentExpressions');
    }
    
    public function testGetUsers() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\[{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users');
    }
    
    public function testGetUser() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/.*Michael Jordan.*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users/1');
    }
    
    public function testLogUserActivity() {
    	$inputObject = '[{"activityKey":"'. UserActivity::ACTIVITY_TEST_ACTIVITY.'","activityValue":"test value"}]';
    	WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputString('{"result":true}');
    	TestingUtil::mockRouterCall($user, 'post', '/api/users/1/activities');
    }
    
    public function testGetUserPreferences() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\[{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users/1/preferences');
    }
    
    public function testSaveUserPreferences() {
        $user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$inputObject = '{"userId":"'.$user->getUserId().'","preferenceKey":"'.UserPreference::PREF_TEST.'","preferenceValue":"test value"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
        $this->expectOutputRegex('/^{.*"userId".*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/users/1/preferences');
    }
    
    public function testGetUserLocations() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\[{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users/1/locations');
    }
    
    public function testGetUserLocation() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/users/1/locations/1');
    }
    
    public function testCreateUserLocation() {
    	$inputObject = '{"userId":"1","locationId":"2","description":"Call Chip at 720.239.2447","isDefaultFlag":"N"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'post', '/api/users/1/locations');
    }
    
    public function testUpdateUserLocation() {
    	$inputObject = '{"userId":"1","locationId":"2","description":"Call Chip at 720.239.2448","isDefaultFlag":"N"}';
    	WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'patch', '/api/users/1/locations/2');
    }
    
    public function testDeleteUserLocation() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
    	$this->expectOutputRegex('/^\{"userId".*/');
    	TestingUtil::mockRouterCall($user, 'delete', '/api/users/1/locations/2');
    }
    
    public static function tearDownAfterClass() {
    	TestingUtil::executeSQL("delete from ".MYSQL_DBNAME.".srvc_user_preferences where preference_key = '".UserPreference::PREF_TEST."' and user_id = 1");
    }
    
}