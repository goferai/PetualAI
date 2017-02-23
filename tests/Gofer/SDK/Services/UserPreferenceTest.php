<?php

use Gofer\SDK\Services\UserPreference;
use Gofer\SDK\Services\UserPreferenceBuilder;
use Gofer\SDK\Services\UserPreferenceService;
use Gofer\SDK\Services\UserPreferenceServiceOptions;

class UserPreferenceTest extends PHPUnit_Framework_TestCase {
	
	public function testInitializeForJSON() {
		$data = new \stdClass();
		$data->userId = 11;
		$data->preferenceKey = UserPreference::PREF_TIMEZONE;
		$data->preferenceValue = 'America/Los_Angeles';
        $userPreferenceBuilder = new UserPreferenceBuilder();
        /** @var UserPreference $userPreference */
		$userPreference = $userPreferenceBuilder->buildForData($data);
		$this->assertEquals($data->preferenceValue, $userPreference->getPreferenceValue());
		$this->assertEquals($data->preferenceKey, $userPreference->getPreferenceKey());
		$this->assertEquals($data->userId, $userPreference->getUserId());
	}
	
	public function testInitializeNew() {
		$data = new \stdClass();
		$data->userId = 11;
		$data->preferenceKey = UserPreference::PREF_TIMEZONE;
		$data->preferenceValue = 'America/Los_Angeles';
        $userPreferenceBuilder = new UserPreferenceBuilder();
        /** @var UserPreference $userPreference */
        $userPreference = $userPreferenceBuilder
            ->setUserId($data->userId)
            ->setPreferenceKey($data->preferenceKey)
                ->setPreferenceValue($data->preferenceValue)
                ->build();
		$this->assertEquals($data->preferenceValue, $userPreference->getPreferenceValue());
		$this->assertEquals($data->preferenceKey, $userPreference->getPreferenceKey());
		$this->assertEquals($data->userId, $userPreference->getUserId());
	}

	public function testGetTimezoneForUser() {
	    $userPreferenceService = new UserPreferenceService();
        $options = (new UserPreferenceServiceOptions())
            ->setUserId(2)
            ->setPreferenceKey(UserPreference::PREF_TIMEZONE);
        $timezone = $userPreferenceService->get($options)->getPreferenceValue();
		$this->assertEquals('America/Los_Angeles', $timezone);
	}
	
	public function testGetForUser() {
        $userPreferenceService = new UserPreferenceService();
        $options = (new UserPreferenceServiceOptions())->setUserId(2);
        $userPreferenceList = $userPreferenceService->getList($options);
		$this->assertGreaterThanOrEqual(1, $userPreferenceList->toArray());
	}
	
	public function insert() {
		$data = new \stdClass();
		$data->userId = -1;
		$data->preferenceKey = UserPreference::PREF_TIMEZONE;
		$data->preferenceValue = 'America/Los_Angeles';

        $userPreferenceBuilder = new UserPreferenceBuilder();
        /** @var UserPreference $userPreference */
        $userPreference = $userPreferenceBuilder->buildForData($data);
        $userPreferenceService = new UserPreferenceService();
        $userPreferenceService->insert($userPreference);
		$this->assertEquals($data->user_id, $userPreference->getUserId());
	}
	
	public static function tearDownAfterClass() {
		\Gofer\Util\TestingUtil::executeSQL("delete from ".MYSQL_DBNAME.".srvc_user_preferences where user_id = -1");
	}
	
}