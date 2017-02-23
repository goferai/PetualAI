<?php

use Gofer\Messages\IntentsAdapter\ApiAiMessageIntentsAdapter;
use Gofer\Util\TestingUtil;

class ApiAiMessageIntentsAdapterTest extends PHPUnit_Framework_TestCase {
	
	public function test_getMessageOutcomeList() {
		$messageIntentsAdapter = new ApiAiMessageIntentsAdapter();
		$messageOutcomeList = $messageIntentsAdapter
            ->setUser(TestingUtil::getTestUser())
            ->setText('what commands can i say')
            ->run()
            ->getMessageOutcomeList();
        $this->assertGreaterThanOrEqual(1, $messageOutcomeList->count());
        $this->assertNotEquals(false, $messageOutcomeList->firstMessageIntent());
		$this->assertEquals('gofer_things_i_can_say', $messageOutcomeList->firstMessageIntent()->getIntent()->getIntentKey());
	}

	public function test_isMissingRequiredEntities() {
	    $messageIntentsAdapter = new ApiAiMessageIntentsAdapter();
        $messageIntentsAdapter->setUser(TestingUtil::getTestUser())->setText('open contact')->run();
        $this->assertEquals(true, $messageIntentsAdapter->isMissingRequiredEntities());
    }

    public function test_getAutomatedResponseText() {
        $messageIntentsAdapter = new ApiAiMessageIntentsAdapter();
        $messageIntentsAdapter->setUser(TestingUtil::getTestUser())->setText('open contact')->run();
        $this->assertGreaterThanOrEqual(10, strlen($messageIntentsAdapter->getAutomatedResponseText()));
    }

    public function test_hasAutomaticResponseText() {
        $messageIntentsAdapter = new ApiAiMessageIntentsAdapter();
        $messageIntentsAdapter->setUser(TestingUtil::getTestUser())->setText('open contact')->run();
        $this->assertEquals(true, $messageIntentsAdapter->hasAutomaticResponseText());
    }
	
}