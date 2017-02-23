<?php

use Gofer\Util\Neo4jConnectionManager;

class Neo4jConnectionManagerTest extends PHPUnit_Framework_TestCase {
	
    public function test_populateParamsInQueryString() {
    	$params = ['params' => [
    			'className' => 'abc',
    			'epsilon' => 123,
    			'someFlag' => true
    	]];
    	$query = "match p where c = {params}.className and e = {params}.epsilon and t = {params}.someFlag";
    	$result = Neo4jConnectionManager::populateParamsInQueryString($query, $params);
    	$this->assertEquals("match p where c = 'abc' and e = 123 and t = true", $result);
    }
    
    public function test_populateParamsInQueryString2() {
    	$params = [
    			'className' => 'abc',
    			'epsilon' => 123,
    			'someFlag' => true
    	];
    	$query = "match p where c = {className} and e = {epsilon} and t = {someFlag}";
    	$result = Neo4jConnectionManager::populateParamsInQueryString($query, $params);
    	$this->assertEquals("match p where c = 'abc' and e = 123 and t = true", $result);
    }

}