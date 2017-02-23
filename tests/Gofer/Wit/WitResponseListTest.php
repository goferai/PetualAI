<?php

use Gofer\Wit\WitRequest;

class WitResponseListTest extends PHPUnit_Framework_TestCase {
	
    public function test_ForEach() {
        $witRequest = new WitRequest();
        $witRequest->setText('take a screenshot');
        $witResponseList = $witRequest->query();
        $array = $witResponseList->toArray();
        $this->assertInternalType('array', $array);
        foreach($witResponseList as $witResponse) {
            $this->assertEquals(\Gofer\Wit\WitResponse::class, get_class($witResponse));
        }
    }

}