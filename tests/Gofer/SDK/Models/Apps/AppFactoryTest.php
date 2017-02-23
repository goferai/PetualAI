<?php

use Gofer\SDK\Models\Apps\AppFactory;
use Gofer\SDK\Models\Apps\GoogleApp;

class AppFactoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider buildForIdProvider
     * @param $id
     * @param $expected
     */
    public function test_buildForId($id, $expected) {
        $app = AppFactory::buildForId($id);
        $this->assertInstanceOf($expected, $app);
    }

    public function buildForIdProvider() {
        return [
            [2, \Gofer\SDK\Models\Apps\SalesforceApp::class],
            ['2', \Gofer\SDK\Models\Apps\SalesforceApp::class],
        ];
    }

    public function test_buildForName() {
        $app =  AppFactory::buildForName('Google');
        $this->assertInstanceOf(GoogleApp::class, $app);
    }
	
}