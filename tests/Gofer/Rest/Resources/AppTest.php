<?php

use Gofer\Util\TestingUtil;

class AppTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;
	
	public function testGetApps() {
		$user = TestingUtil::getTestAdminUser();
    	$this->expectOutputRegex('/^\[{"appId":".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/apps');
    }
    
    public function testGetApp() {
    	$user = TestingUtil::getUser(TestingUtil::$mainTestAdminEmail);
    	$this->expectOutputRegex('/^\{"appId":"1".*/');
    	TestingUtil::mockRouterCall($user, 'get', '/api/apps/1');
    }

    public function test_verifyPendingUserLink() {
	    //create the pendingUserLink
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $appUserId = 'test_verifyPendingUserLink';
        $user = TestingUtil::getUser(TestingUtil::$mainTestUserEmail);
        TestingUtil::executeSQL("
            insert into gofer.srvc_app_pending_user_links (app_id, app_user_id, link_code, expiration_date) 
            values (1, '$appUserId', '$uuid', date_add(now(), INTERVAL 5 DAY) ) on duplicate key update link_code = values(link_code), expiration_date = VALUES(expiration_date); 
            delete from gofer.srvc_user_app_user_ids where user_id = {$user->getUserId()} and app_user_id = '$appUserId';"
        );
        $this->expectOutputString('{"result":true, "message":"Link verified successfully."}');
        TestingUtil::mockRouterCall($user, 'post', '/api/apps/1/pendingUserLinks/'.$uuid);
    }
    
//     public function testPostApp() {
//     	$inputObject = '{"app_name":"test app","web_service_url":"test web_service_url","web_service_url_dev":"test web_service_url_dev","app_id":"9999"}';
//     	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//     	$user = \Gofer\Util\TestingUtil::getUser(\Gofer\Util\TestingUtil::$mainTestAdminEmail);
//     	$this->expectOutputString($inputObject);
//     	\Gofer\Util\TestingUtil::mockRouterCall($user, 'post', '/api/apps');
//     }
    
    public static function tearDownAfterClass() {
    	TestingUtil::executeSQL('
            delete from '.MYSQL_DBNAME.'.srvc_apps where app_id = 9999; 
            delete from '.MYSQL_DBNAME.'.srvc_apps where app_id = 9999;');
    	$log = new \Gofer\Util\Log(basename(__FILE__));
    	$log->debug("tearDownAfterClass AppTest");
    }
    
}