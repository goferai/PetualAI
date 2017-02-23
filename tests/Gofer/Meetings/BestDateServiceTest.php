<?php

use Gofer\Meetings\BestDate;
use Gofer\Meetings\BestDateService;
use Gofer\SDK\Services\MeetingAttendeeService;
use Gofer\SDK\Services\MeetingAttendeeServiceOptions;
use Gofer\SDK\Services\MeetingService;
use Gofer\SDK\Services\MeetingServiceOptions;
use Gofer\Util\TestingUtil;

class BestDateServiceTest extends PHPUnit_Framework_TestCase {
	
    public function test_lookupBestDates() {
        $meetingId = '1ccab3ef-62bb-41fb-8566-301d28fc5e75';
        TestingUtil::emptyTablesForMeeting();
        TestingUtil::moveTestDataForMeetingID($meetingId);
        $meeting = (new MeetingService())->get((new MeetingServiceOptions())->setMeetingId($meetingId));
        $meetingAttendeeList = (new MeetingAttendeeService())->getList((new MeetingAttendeeServiceOptions())->setMeetingId($meetingId));
        $bestDateService = new BestDateService();
        $bestDateService
            ->setMeetingID($meeting->getMeetingId())
            ->setDurationMinutes($meeting->getDurationMinutes())
            ->setRangeDaysOut(30)
            ->setIgnoreBlockedTimes(true)
            ->setRequireEveryone(false)
            ->setAttendeesAutoPicking($meetingAttendeeList->filterForStates(\Gofer\SDK\Services\MeetingAttendee::STATE_AUTO_PICK)->toArray())
            ->lookupBestDates();
        $this->assertTrue($bestDateService->foundBestDate());
        $this->assertGreaterThanOrEqual(3, count($bestDateService->getBestDateResults()));
        $this->assertInstanceOf(BestDate::class, $bestDateService->getBestDateResults()[0]);
    }
    
}