<?php

//use Gofer\Exceptions\AppNotActivatedException;
//use Gofer\SDK\Services\UserAppService;
//use Gofer\SDK\Services\UserAppServiceOptions;
//use Gofer\Util\TestingUtil;
//use Gofer\Outlook\OutlookClient;

class OutlookClientTest extends PHPUnit_Framework_TestCase {

    public function test_filler() {
        $this->assertEquals(1,1);
    }

    //Not used
//	public function test_tryRefreshAccessToken() {
//		$user = TestingUtil::getTestAdminUser();
//        $userAppService = new UserAppService();
//        $userApp = $userAppService->get((new UserAppServiceOptions())->setUserId($user->getUserId())->setAppId(5));
//        if(!$userApp) throw new AppNotActivatedException();
//		$outlookClient = new OutlookClient($user, $userApp);
//		$result = $outlookClient->tryRefreshAccessToken();
//		$this->assertEquals(true, $result);
//		//Note: can't test if they are different because if you try to refresh an access token that is still valid it just returns the same token again.
//	}
	
}