<?php

use Gofer\SDK\Services\MeetingBuilder;

class MeetingBuilderTest extends PHPUnit_Framework_TestCase {

	public function test_Build() {
        $email = new \Gofer\SDK\Services\Email();
        $email->setEmailFrom('Chip Allen <chip@gofer.co>');
        $meetingBuilder = new MeetingBuilder();
        $meetingBuilder
            ->setUserId(\Gofer\Util\TestingUtil::$mainTestAdminUserID)
            ->setTimezone(\Gofer\Util\DateUtil::TZ_PST)
            ->setEmail($email);
        $meeting = $meetingBuilder->build();
        $this->assertEquals('720.239.2447', $meeting->getLocation());
    }

}