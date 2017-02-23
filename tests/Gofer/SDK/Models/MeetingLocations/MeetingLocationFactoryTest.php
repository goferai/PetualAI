<?php

use Gofer\SDK\Models\MeetingLocations\MeetingLocationFactory;
use Gofer\SDK\Models\MeetingLocations\SkypeCallMeetingLocation;

class MeetingLocationFactoryTest extends PHPUnit_Framework_TestCase {
	
	public function test_buildForLocationId() {
        $meetingLocationFactory = new MeetingLocationFactory();
        $location = $meetingLocationFactory->buildForLocationId(1);
		$this->assertInstanceOf(SkypeCallMeetingLocation::class, $location);
	}

    public function test_buildForName() {
        $meetingLocationFactory = new MeetingLocationFactory();
        $location = $meetingLocationFactory->buildForName('Skype call');
        $this->assertInstanceOf(SkypeCallMeetingLocation::class, $location);
    }
	
}