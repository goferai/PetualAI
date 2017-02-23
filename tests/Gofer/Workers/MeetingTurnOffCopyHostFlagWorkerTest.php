<?php

use Gofer\SDK\Services\UserPreference;
use Gofer\SDK\Services\UserPreferenceBuilder;
use Gofer\SDK\Services\UserPreferenceService;
use Gofer\Util\EmailUtil;
use Gofer\Util\TestingUtil;

class MeetingTurnOffCopyHostFlagWorkerTest extends PHPUnit_Framework_TestCase
{
	
	public function testRun() {
        EmailUtil::$fakeSendEmailsForTesting = true;
		$userPreferenceService = new UserPreferenceService();
        $userPreferenceBuilder = new UserPreferenceBuilder();
        $userPreference = $userPreferenceBuilder
            ->setUserId(TestingUtil::$mainTestUserID)
            ->setPreferenceKey(UserPreference::PREF_BCC_ON_GOFER_MEETING_EMAILS)
            ->setPreferenceValue('Y')
            ->build();
        $userPreferenceService->upsert($userPreference);

        $userPreference = $userPreferenceBuilder
            ->setUserId(TestingUtil::$mainTestUserID)
            ->setPreferenceKey(UserPreference::PREF_TURN_OFF_BCC_SETTING_EMAIL_SENT)
            ->setPreferenceValue('N')
            ->build();
        $userPreferenceService->upsert($userPreference);
		
		$meetingTurnOffCopyHostFlagWorker = new \Gofer\Workers\MeetingTurnOffCopyHostFlagWorker();
		$result = $meetingTurnOffCopyHostFlagWorker->run();
		$this->assertInternalType('array', $result);
		$this->assertGreaterThanOrEqual(1, $result);
	}
	
	public function testRun_EmailAlreadySent() {
		EmailUtil::$fakeSendEmailsForTesting = true;

        $userPreferenceService = new UserPreferenceService();
        $userPreferenceBuilder = new UserPreferenceBuilder();
        $userPreference = $userPreferenceBuilder
            ->setUserId(TestingUtil::$mainTestUserID)
            ->setPreferenceKey(UserPreference::PREF_BCC_ON_GOFER_MEETING_EMAILS)
            ->setPreferenceValue('Y')
            ->build();
        $userPreferenceService->upsert($userPreference);


        $userPreference = $userPreferenceBuilder
            ->setUserId(TestingUtil::$mainTestUserID)
            ->setPreferenceKey(UserPreference::PREF_TURN_OFF_BCC_SETTING_EMAIL_SENT)
            ->setPreferenceValue('N')
            ->build();
        $userPreferenceService->upsert($userPreference);
	
		$meetingTurnOffCopyHostFlagWorker = new \Gofer\Workers\MeetingTurnOffCopyHostFlagWorker();
		$meetingTurnOffCopyHostFlagWorker->run();

		//Simulate the user changing this setting back
        $userPreference = $userPreferenceBuilder
            ->setUserId(TestingUtil::$mainTestUserID)
            ->setPreferenceKey(UserPreference::PREF_BCC_ON_GOFER_MEETING_EMAILS)
            ->setPreferenceValue('Y')
            ->build();
        $userPreferenceService->upsert($userPreference);
		
		//Now make sure we do NOT send any emails this time since we already did
		$meetingTurnOffCopyHostFlagWorker = new \Gofer\Workers\MeetingTurnOffCopyHostFlagWorker();
		$result2 = $meetingTurnOffCopyHostFlagWorker->run();
		
		$this->assertInternalType('array', $result2);
		$this->assertCount(0, $result2);
	}
	
}