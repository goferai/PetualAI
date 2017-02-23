<?php

class GoferWebsitePageUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider accountProvider
     * @param $tab
     * @param $expected
     */
	public function test_account($tab, $expected) {
		$this->assertEquals($expected, \Gofer\Util\GoferWebsitePageUtil::account($tab));
	}
	
	public function accountProvider() {
		return [
		    [null, GOFER_SITE_BASE_URL.'/account'],
            [\Gofer\Util\GoferWebsitePageUtil::ACCOUNT_TAB_CONNECTED_APPS, GOFER_SITE_BASE_URL.'/account?tab=connectedApps']
        ];
	}

}