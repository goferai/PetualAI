<?php

use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;
use Gofer\Workers\UserPlanDowngradeWorker;

class UserPlanDowngradeWorkerTest extends PHPUnit_Framework_TestCase {
	
    public function test_run() {
        TestingUtil::executeSQL('delete from srvc_user_plans where user_id = -1');
        $startDate = DateUtil::getCurrentDateTimeUTC()->modify('-40 days')->format(DateUtil::FORMAT_ISO8601);
        TestingUtil::executeSQL("insert into srvc_user_plans (user_id, plan_id, start_date, trial_period_days, downgrade_to_plan_id) values (-1, 1, '$startDate', 30, 3)");

        $worker = new UserPlanDowngradeWorker();
        $results = $worker->run();
        $this->assertInternalType('array', $results);
        $this->assertGreaterThanOrEqual(1, $results);
        $data = TestingUtil::getMultipleRows('select * from srvc_user_plans where user_id = -1 order by plan_id');
        $this->assertEquals(2, count($data));
        $this->assertEquals('N', $data[0]->active_flag);
        $this->assertEquals('Y', $data[1]->active_flag);
    }

}