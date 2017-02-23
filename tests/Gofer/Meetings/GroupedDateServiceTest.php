<?php

use Gofer\Meetings\GroupedDate;
use Gofer\Meetings\GroupedDateService;
use Gofer\Meetings\GroupedDateServiceOptions;
use Gofer\SDK\Services\MeetingAttendeeDate;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class GroupedDateServiceTest extends PHPUnit_Framework_TestCase {
	
    public function test_getList_AcceptedAndRejected() {
        $meetingId = 'b11c9fa5-4b52-4e45-b68c-0382eef7b9d6';
        TestingUtil::emptyTablesForMeeting();
        TestingUtil::moveTestDataForMeetingID($meetingId);
        $groupedDateService = new GroupedDateService();
        $groupedDateList = $groupedDateService->getList((new GroupedDateServiceOptions())
            ->setMeetingID($meetingId)
            ->setResponses([MeetingAttendeeDate::RESPONSE_ACCEPTED, MeetingAttendeeDate::RESPONSE_REJECTED])
            ->setConvertToLocalTimezone(DateUtil::TZ_PST));
        $this->assertGreaterThanOrEqual(1, $groupedDateList->count());
        $this->assertInstanceOf(GroupedDate::class, $groupedDateList->first());
        $this->assertGreaterThanOrEqual(1, count($groupedDateList->first()->getMeetingAttendeeDates()));
    }
    
}