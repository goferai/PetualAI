<?php

use Gofer\Messages\IntentsAdapter\WitMessageIntentsAdapter;
use Gofer\Util\TestingUtil;

class WitMessageIntentsAdapterTest extends PHPUnit_Framework_TestCase {
	
	public function test_getIntents() {
		$witMessageIntentsAdapter = new WitMessageIntentsAdapter();
		$messageOutcomeList = $witMessageIntentsAdapter
            ->setUser(TestingUtil::getTestUser())
            ->setText('Take a screenshot')
            ->run()
            ->getMessageOutcomeList();
        $this->assertGreaterThanOrEqual(1, $messageOutcomeList->count());
        $this->assertNotEquals(false, $messageOutcomeList->firstMessageIntent());
		$this->assertEquals('gofer_screenshot', $messageOutcomeList->firstMessageIntent()->getIntent()->getIntentKey());
	}
	
}