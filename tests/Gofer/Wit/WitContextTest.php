<?php

class WitContextTest extends PHPUnit_Framework_TestCase
{
    public function testInitializeForUserID()
    {
    	$witContext = new \Gofer\Wit\WitContext();
    	$contextURLString = $witContext->initializeForUserID(2)->toString();
    	$expectedURLString = '&context=%7B%22timezone%22%3A%22America%5C%2FLos_Angeles%22%7D';
    	$this->assertEquals($expectedURLString, $contextURLString);
    }
    
    public function testInitializeForUserID2()
    {
    	$witContext = new \Gofer\Wit\WitContext();
    	$contextURLString = $witContext->initializeForUserID(2, "email")->toString();
    	$expectedURLString = '&context=%7B%22state%22%3A%22email%22%2C%22timezone%22%3A%22America%5C%2FLos_Angeles%22%7D';
    	$this->assertEquals($expectedURLString, $contextURLString);
    }
    
    public function testStatesArray()
    {
    	$witContext = new \Gofer\Wit\WitContext();
    	$contextURLString = $witContext->setStates(array(\Gofer\Wit\WitStates::STATE_EMAIL, \Gofer\Wit\WitStates::STATE_REPLY))->toString();
    	$expectedURLString = '&context=%7B%22state%22%3A%5B%22'. \Gofer\Wit\WitStates::STATE_EMAIL.'%22%2C%22'. \Gofer\Wit\WitStates::STATE_REPLY.'%22%5D%7D';
    	$this->assertEquals($expectedURLString, $contextURLString);
    }
    
    public function testTimezone()
    {
    	$witContext = new \Gofer\Wit\WitContext();
    	$contextURLString = $witContext->setTimezone('America/Los_Angeles')->toString();
    	$expectedURLString = '&context=%7B%22timezone%22%3A%22America%5C%2FLos_Angeles%22%7D';
    	$this->assertEquals($expectedURLString, $contextURLString);
    }
    
    public function testStatesInvalidArgument()
    {
    	$witContext = new \Gofer\Wit\WitContext();
    	$this->setExpectedException('InvalidArgumentException');
    	$witContext->setStates(1)->toString();
    }      
    
    public function testEmpty()
    {
    	$witContext = new \Gofer\Wit\WitContext();
    	$contextURLString = $witContext->toString();
    	$this->assertEquals('', $contextURLString);
    }
}