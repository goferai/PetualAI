<?php

use Gofer\SDK\Services\UserBusySlotsAlwaysService;
use Gofer\Util\TestingUtil;

class UserBusySlotAlwaysTest extends PHPUnit_Framework_TestCase {
	
	public static $userID = 9999999;
	
	public function test_setDefaultValuesForUser() {
        $userBusySlotAlwaysService = new UserBusySlotsAlwaysService();
        $userBusySlotAlwaysService->insertDefaultValuesForUser(self::$userID);
		$row = TestingUtil::getSingleRow("select count(*) as cnt from gofer.srvc_user_busy_slots_always where user_id = ".self::$userID);
		$this->assertEquals(472, $row->cnt);
	}
	
	public static function tearDownAfterClass() {
		\Gofer\Util\TestingUtil::executeSQL("delete from ".MYSQL_DBNAME.".srvc_user_busy_slots_always where user_id = ".self::$userID);
	}
	
}