<?php


use Gofer\SDK\Services\UserService;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;
use Gofer\Util\WebServiceUtil;

class RouterTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;

    protected static $user = null;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        if (static::$user === null) {
            $userService = new UserService();
            $user = $userService->get((new \Gofer\SDK\Services\UserServiceOptions())->setEmail('mj@gofer.co'));
            $user->setTrialExpiresDate(DateUtil::getCurrentDateTimeUTC()->modify('-1 minute')->format(DateUtil::FORMAT_ISO8601));
            $userService->update($user);
            static::$user = $user;
        }
    }

    //Remove this feature
//    public function test_trialExpired_Blocked() {
//    	WebServiceUtil::$testingSimulatedRequestBody = '{"text":"open the microsoft account"}';
//    	$this->expectOutputRegex('/[\s\S]*Upgrade Required[\s\S]*/');
//    	TestingUtil::mockRouterCall(static::$user, 'post', '/api/messages');
//    }

    /**
     * Tests if calls that do not require a user like login will still work
     */
    public function test_trialExpired_Allowed_NoUser() {
        WebServiceUtil::$testingSimulatedRequestBody = '{"username":"mj@gofer.co","password":"'.TEST_MJ_USER_PASSWORD.'"}';
        $this->expectOutputRegex('/^{"userId":.*/');
        TestingUtil::mockRouterCall(static::$user, 'post', '/api/auth/login');
    }

    /**
     * Tests if calls that require a user, but they are expired, BUT the allowExpiredUser setting is set on the rest resource
     */
    public function test_trialExpired_Allowed2() {
        $this->expectOutputString('{"Result":true}');
        TestingUtil::mockRouterCall(static::$user, 'get', '/api/test/testAllowExpiredTrialUser');
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        $userService = new UserService();
        $user = $userService->get((new \Gofer\SDK\Services\UserServiceOptions())->setEmail('mj@gofer.co'));
        $user->setTrialExpiresDate(null);
        $userService->update($user);
    }
}