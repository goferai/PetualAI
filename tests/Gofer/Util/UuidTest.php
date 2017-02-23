<?php

use Ramsey\Uuid\Uuid;

class UuidTest extends PHPUnit_Framework_TestCase {

	public function test_uuid4_random()
	{
		$uuids = [];
        for ($i = 0; $i < 10; $i++) {
            array_push($uuids, Uuid::uuid4());
        }
        $counter = 0;
        foreach($uuids as $uuid) {
            $dupeUuids = $uuids;
            unset($dupeUuids[$counter]);
            $this->assertNotContains($uuid, $dupeUuids);
            $counter ++;
        }
	}

}