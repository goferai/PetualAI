<?php

use Gofer\Wit\WitEntityTypes;
use Gofer\Wit\WitRequest;
use Gofer\Wit\WitResponseEntity;
use Gofer\Wit\WitResponseEntityDurationNormalized;

class WitRequestTest extends PHPUnit_Framework_TestCase
{
    public function testSetText()
    {
    	$witRequest = new WitRequest();
    	$text = "something";
    	$witRequest->setText($text);
    	$this->assertEquals($text, $witRequest->getText());
    }
    
    public function testSetContext()
    {
    	$witRequest = new WitRequest();
    	$witContext = new \Gofer\Wit\WitContext();
    	$witRequest = $witRequest->setContext($witContext);
    	$this->assertInstanceOf(WitRequest::class, $witRequest);
    }
    
    public function testQuery_NoEntities() {
    	$witRequest = new WitRequest();
    	$text = "take a screenshot";
    	$witResponseList = $witRequest->setText($text)->query();
        $this->assertCount(1, $witResponseList->toArray());
    	$this->assertEquals("gofer_screenshot", $witResponseList->first()->intent);
    	$this->assertEquals($text, $witResponseList->first()->_text);
    	$this->assertInternalType('double', $witResponseList->first()->confidence);
    	$this->assertGreaterThan(0.9, $witResponseList->first()->confidence);
    }
    
    public function testQuery_Entities() {
    	$witRequest = new WitRequest();
    	$text = "I am copying my assistant Gofer who will arrange a meeting for next week";
    	$witContext = new \Gofer\Wit\WitContext();
    	$witContext->setStates(\Gofer\Wit\WitStates::STATE_EMAIL);
        $witResponseList = $witRequest->setText($text)->setContext($witContext)->query();
        $this->assertCount(1, $witResponseList->toArray());
    	$this->assertEquals("meeting_request", $witResponseList->first()->intent);
        $this->assertCount(1, $witResponseList->first()->getEntitiesOfType(WitEntityTypes::DATETIME));
    	$this->assertInternalType('array', $witResponseList->first()->entities);
    }
    
    public function testQuery_FilterEntities() {
    	$witRequest = new WitRequest();
    	$text = "I am copying my assistant Gofer who will arrange a conference call for next week";
    	$witContext = new \Gofer\Wit\WitContext();
    	$witContext->setStates(\Gofer\Wit\WitStates::STATE_EMAIL);
    	$witResponseList = $witRequest->setText($text)->setContext($witContext)->query();
        $this->assertCount(1, $witResponseList->toArray());
    	$this->assertEquals("meeting_request", $witResponseList->first()->intent);
    	$this->assertCount(1, $witResponseList->first()->getEntitiesOfType(WitEntityTypes::DATETIME));
    }
    
    public function testQuery_MultipleEntities() {
    	$witRequest = new WitRequest();
    	$text = "I am copying my assistant Gofer who will arrange a meeting for Monday or Tuesday";
    	$witContext = new \Gofer\Wit\WitContext();
    	$witContext->setStates(\Gofer\Wit\WitStates::STATE_EMAIL);
    	$witResponseList = $witRequest->setText($text)->setContext($witContext)->query();
        $this->assertCount(1, $witResponseList->toArray());
    	$this->assertEquals("meeting_request", $witResponseList->first()->intent);
    	$this->assertInternalType('array', $witResponseList->first()->entities);
    	$dates = $witResponseList->first()->getEntitiesOfType(WitEntityTypes::DATETIME);
    	$this->assertInternalType('array', $dates);
    	$this->assertCount(2, $dates);
    	$this->assertInstanceOf(WitResponseEntity::class, $dates[0]);
    }
    
    public function testQuery_DurationEntity() {
    	$witRequest = new WitRequest();
    	$text = "I am copying my assistant Gofer who will arrange a 15-min meeting for tomorrow";
    	$witContext = new \Gofer\Wit\WitContext();
    	$witContext->setStates(\Gofer\Wit\WitStates::STATE_EMAIL);
    	$witResponseList = $witRequest->setText($text)->setContext($witContext)->query();
        $this->assertCount(1, $witResponseList->toArray());
    	$this->assertEquals("meeting_request", $witResponseList->first()->intent);
    	$this->assertInternalType('array', $witResponseList->first()->entities);
    	$this->assertCount(2, $witResponseList->first()->entities);
    	$this->assertEquals('duration', $witResponseList->first()->entities[0]->name);
    	$this->assertInstanceOf(WitResponseEntityDurationNormalized::class, $witResponseList->first()->entities[0]->normalized);
    	$this->assertEquals(900, $witResponseList->first()->entities[0]->normalized->value);
    }
}