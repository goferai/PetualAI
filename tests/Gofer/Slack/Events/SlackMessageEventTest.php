<?php

use Gofer\Slack\Events\SlackEventFactory;
use Gofer\Util\TestingUtil;

class SlackMessageEventTest extends PHPUnit_Framework_TestCase
{
    public function test_run() {
        TestingUtil::$fakeSendSlackMessages = true;
    	$data = '{"type":"message","channel":"D2FF7C5F1","user":"U1C3YR407","text":"what things can I say","ts":"1474691786.000006","event_ts": "1474691786.000006"}';
    	$slackEventFactory = new SlackEventFactory();
		$slackMessageEvent = $slackEventFactory->build($data);
    	$slackMessageEvent->run();
    	$this->assertEquals(1, 1);
    }

    public function test_run_groupChannelMention() {
        TestingUtil::$fakeSendSlackMessages = true;
        $data = '{"team_id":"message","type":"message","channel":"C1C3V7AHW","user":"U1C3YR407","text":"<@U2FDFH5GT> what things can I say?","ts":"1474691786.000006","event_ts": "1474691786.000006"}';
        $slackEventFactory = new SlackEventFactory();
        $slackMessageEvent = $slackEventFactory->build($data);
        $slackMessageEvent->run();
        $this->assertEquals(1, 1);
    }
    
}