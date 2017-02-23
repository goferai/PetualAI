<?php

use Gofer\Calendar\BusyDate;
use Gofer\Calendar\BusyDateList;
use Gofer\Util\DateUtil;


class DateOverlapRemoverTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider removeOverlapProvider
     * @param $starts
     * @param $ends
     * @param $expectedStarts
     * @param $expectedEnds
     */
    public function test_removeOverlap($starts, $ends, $expectedStarts, $expectedEnds) {
    	$index = 0;
    	$busyDateList = new BusyDateList();
    	foreach($starts as $start) {
    	    $busyDate = new BusyDate();
            $busyDate->setUserId(\Gofer\Util\TestingUtil::$mainTestUserID);
            $busyDate->setStart($start);
            $busyDate->setEnd($ends[$index]);
    		$busyDate->setTimezone(DateUtil::TZ_PST);
            $busyDateList->add($busyDate);
    		$index++;
    	}
        $busyDateList->removeOverlap();
    	$this->assertEquals(count($expectedStarts), $busyDateList->count());
        $index = 0;
        foreach ($busyDateList as $busyDate) {
            $this->assertEquals($expectedStarts[$index], $busyDate->getStart());
            $this->assertEquals($expectedEnds[$index], $busyDate->getEnd());
            $index++;
        }
    }

    public function removeOverlapProvider() {
        return [
            //Simple
            [
                [
                    '2016-05-09 10:00:00',
                    '2016-05-09 10:30:00',
                    '2016-05-09 12:00:00',
                    '2016-05-09 13:00:00',
                    '2016-05-09 08:00:00',
                    '2016-05-09 11:30:00'
                ],
                [
                    '2016-05-09 10:30:00',
                    '2016-05-09 11:00:00',
                    '2016-05-09 14:00:00',
                    '2016-05-09 13:15:00',
                    '2016-05-09 08:30:00',
                    '2016-05-09 12:15:00'
                ],
                [
                    '2016-05-09T08:00:00-07:00',
                    '2016-05-09T10:00:00-07:00',
                    '2016-05-09T11:30:00-07:00',
                ],
                [
                    '2016-05-09T08:30:00-07:00',
                    '2016-05-09T11:00:00-07:00',
                    '2016-05-09T14:00:00-07:00',
                ],
            ],
            //Complex
            [
                [
                    '2016-05-09 10:00:00',
                    '2016-05-09 08:00:00',
                    '2016-05-09 08:30:00',
                    '2016-05-09 09:00:00',
                    '2016-05-09 09:30:00'
                ],
                [
                    '2016-05-09 10:30:00',
                    '2016-05-09 08:30:00',
                    '2016-05-09 09:00:00',
                    '2016-05-09 09:30:00',
                    '2016-05-09 10:00:00'
                ],
                [
                    '2016-05-09T08:00:00-07:00',
                ],
                [
                    '2016-05-09T10:30:00-07:00',
                ],
            ],
        ];
    }

}