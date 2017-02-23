<?php

use Gofer\SDK\Services\IntentService;
use Gofer\SDK\Services\IntentServiceOptions;
use Gofer\Util\TestingUtil;

class IntentTest extends PHPUnit_Framework_TestCase {
	
	public function test_get_gofer_open_website() {
        $intentService = new IntentService();
        $intentServiceOptions = new IntentServiceOptions();
        $intentServiceOptions->setOnlyIntentsAllowedForUserID(TestingUtil::$mainTestUserID)->setIntentKey('gofer_open_website');
        $intent = $intentService->get($intentServiceOptions);
		$this->assertNotEquals(false, $intent);
        $this->assertEquals('gofer_open_website', $intent->getIntentKey());
	}
	
}