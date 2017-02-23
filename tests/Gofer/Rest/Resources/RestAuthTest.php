<?php

use Gofer\SDK\Services\UserService;
use Gofer\SDK\Services\UserServiceOptions;
use Gofer\Stormpath\StormpathApplication;
use Gofer\Util\Auth\ForgotPasswordData;
use Gofer\Util\Log;
use Gofer\Util\SQL\SQLConnection;
use Gofer\Util\TestingUtil;
use Gofer\Util\WebServiceUtil;
use Stormpath\Resource\Account;

class RestAuthTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;

    /**
     * @dataProvider loginData
     * @param $username
     * @param $password
     * @param $expected
     */
    public function test_login_Body($username, $password, $expected) {
        $log = new Log(basename(__FILE__));
        $log->debug('test_login_Body 1');
    	$inputObject = '{"username":"'.$username.'","password":"'.$password.'"}';
        $log->debug('test_login_Body 2');
    	WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
        $log->debug('test_login_Body 3');
    	$this->expectOutputRegex($expected);
        $log->debug('test_login_Body 4');
    	TestingUtil::mockRouterCall(null, 'post', '/api/auth/login');
    }

    /**
     * @dataProvider loginData
     * @param $username
     * @param $password
     * @param $expected
     */
    public function test_login_urlParams($username, $password, $expected) {
        WebServiceUtil::$testingSimulatedRequestBody = 'username='.$username.'&password='.$password;
        $this->expectOutputRegex($expected);
        TestingUtil::mockRouterCall(null, 'post', '/api/auth/login');
    }

    public function loginData() {
        return array(
            array("mj@gofer.co", TEST_MJ_USER_PASSWORD, '/^{"userId":.*/'),
        );
    }

    /**
     * @dataProvider forgotData
     * @param $email
     * @param $expected
     */
    public function test_forgot($email, $expected) {
        $forgotPasswordData = new ForgotPasswordData();
        $forgotPasswordData->setEmail($email);
        WebServiceUtil::$testingSimulatedRequestBody = $forgotPasswordData->toJSON();
        $this->expectOutputRegex($expected);
        TestingUtil::mockRouterCall(null, 'post', '/api/auth/forgot');
    }

    public function forgotData() {
        return array(
            array("bad@gofer.co", '/^"bad@gofer\.co"/'),
            array("mj@gofer.co", '/^"mj@gofer\.co"/'),
        );
    }

    //TODO: Turn back on once stormpath is working again
//    /**
//     * @dataProvider registerData
//     * @param $email
//     * @param $givenName
//     * @param $surname
//     * @param $password
//     * @param $expected
//     */
//    public function test_Register($email, $givenName, $surname, $password, $expected) {
//        $userRegisterData = new UserRegisterData();
//        $userRegisterData->setEmail($email);
//        $userRegisterData->setGivenName($givenName);
//        $userRegisterData->setSurname($surname);
//        $userRegisterData->setPassword($password);
//        WebServiceUtil::$testingSimulatedRequestBody = $userRegisterData->toJSON(true);
//        $this->expectOutputRegex($expected);
//        TestingUtil::mockRouterCall(null, 'post', '/api/auth/register');
//        $userService = new UserService();
//        $user = $userService->get((new UserServiceOptions())->setEmail($email));
//        $this->assertNotNull($user);
//        $this->assertNotFalse($user);
//
//        //assert trial expires date was set
//        $userPlan = TestingUtil::getSingleRow("select * from gofer.srvc_user_plans where user_id = '".$user->getUserId()."' and active_flag = 'Y'");
//        $this->assertNotFalse($userPlan);
//        $this->assertEquals(PlusTrialPlan::PLAN_ID, $userPlan->plan_id);
//
//        //assert org was made
//        $userOrgList = (new UserOrgService())->getList((new UserOrgServiceOptions())->setUserId($user->getUserId()));
//        $this->assertEquals(1, $userOrgList->count());
//        $this->assertEquals(DefaultOrg::ORG_ID, $userOrgList->first()->getOrgId());
//
//        //assert they were given the user role
//        $userOrgRoleList = (new UserOrgRoleService())->getList((new UserOrgRoleServiceOptions())->setUserId($user->getUserId()));
//        $this->assertEquals(1, $userOrgRoleList->count());
//        $this->assertEquals(UserRole::ROLE_ID, $userOrgRoleList->first()->getRoleId());
//
//        //assert they were given a free trial user plan
//        $userPlanList = (new UserPlanService())->getList((new UserPlanServiceOptions())->setUserId($user->getUserId()));
//        $this->assertEquals(1, $userPlanList->count());
//        $this->assertEquals(PlusTrialPlan::PLAN_ID, $userPlanList->first()->getPlanId());
//
//        //assert they were assigned the default preferences
//        $userPreferencesList = (new UserPreferenceService())->getList((new UserPreferenceServiceOptions())->setUserId($user->getUserId()));
//        $this->assertEquals(3, $userPreferencesList->count());
//        $this->assertTrue($userPreferencesList->hasPreferenceKey(UserPreference::PREF_DEFAULT_MEETING_DURATION));
//        $this->assertEquals('30', $userPreferencesList->resetFilter()->filterPreferenceKeys(UserPreference::PREF_DEFAULT_MEETING_DURATION)->first()->getPreferenceValue());
//
//        $this->assertTrue($userPreferencesList->resetFilter()->hasPreferenceKey(UserPreference::PREF_BCC_ON_GOFER_MEETING_EMAILS));
//        $this->assertEquals('Y', $userPreferencesList->resetFilter()->filterPreferenceKeys(UserPreference::PREF_BCC_ON_GOFER_MEETING_EMAILS)->first()->getPreferenceValue());
//
//        $this->assertTrue($userPreferencesList->resetFilter()->hasPreferenceKey(UserPreference::PREF_TIMEZONE));
//        $this->assertEquals(DateUtil::TZ_PST, $userPreferencesList->resetFilter()->filterPreferenceKeys(UserPreference::PREF_TIMEZONE)->first()->getPreferenceValue());
//    }

    public function registerData() {
        return array(
            array("testregister@gofer.co", 'TestGivenName', 'TestSurname', 'Abcd123!', '/^{"userId":.*/'),
        );
    }

    public static function tearDownAfterClass() {
        StormpathApplication::getInstance()->application();
        $userService = new UserService();
        $user = $userService->get((new UserServiceOptions())->setEmail('testregister@gofer.co'));
        if ($user) {
            $userId = $user->getUserId();
            $sql = "
                delete from gofer.srvc_user_preferences where user_id = $userId;
                delete from gofer.srvc_user_app_user_ids where user_id = $userId;
                delete from gofer.srvc_user_apps where user_id = $userId;
                delete from gofer.srvc_user_calendars where user_id = $userId;
                delete from gofer.srvc_user_orgs where user_id = $userId;
                delete from gofer.srvc_user_org_roles where user_id = $userId;
                delete from gofer.srvc_user_phones where user_id = $userId;
                delete from gofer.srvc_user_plans where user_id = $userId;
                delete from gofer.srvc_users where user_id = $userId;
            ";
            SQLConnection::getInstance()->exec($sql);
            /** @var Account $account */
            if ($user->getHref()) {
                $account = Account::get($user->getHref());
                if ($account) {
                    $account->delete();
                }
            }
        }
    }

    //cannot test because there is no HOST set when called thru phpunit
//     public function testLogout() {
//     	$user = \Gofer\Util\TestingUtil::getUser(\Gofer\Util\TestingUtil::$mainTestUserEmail);
    	 
//     	$this->expectOutputString('{"Result":"True"}');
    	 
//     	$resource = new \Gofer\Rest\Resources\Auth(array(
//     		array('method'=>'get', 'pattern'=>'/api/logout', 'function'=>'logout', 'requireUser'=>true)
//     	));
//     	\Gofer\Util\TestingUtil::mockRouterCall($resource, $user, '/api/logout', 'get', 'logout');
//     }

}