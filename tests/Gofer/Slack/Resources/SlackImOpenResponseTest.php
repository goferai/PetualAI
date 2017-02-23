<?php

use Gofer\Slack\Responses\SlackImOpenResponse;

class SlackImOpenResponseTest extends PHPUnit_Framework_TestCase
{
    public function test_Run()
    {
    	$data = '{"ok":true,"no_op":true,"already_open":true,"channel":{"id":"D2FF7C5F1"}}';
    	$slackImOpenResponse = new SlackImOpenResponse();
        $slackImOpenResponse->initializeForData(json_decode($data));
    	$this->assertEquals("D2FF7C5F1", $slackImOpenResponse->getChannel()->getId());
    }
    
}