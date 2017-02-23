<?php

use Gofer\Util\GooglePlaceUtil;

class GooglePlaceUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider nullProvider
     * @param $companyName
     */
    public function testNull($companyName) {
        $response = GooglePlaceUtil::search($companyName);
        $this->assertNull($response);
    }

    public function nullProvider() {
        return [
            ['DFASFLKJKJLKJSKAFSD'],
        ];
    }

    /**
     * @dataProvider phoneProvider
     * @param $companyName
     * @param $expected
     */
    public function testPhone($companyName, $expected) {
    	$response = GooglePlaceUtil::search($companyName);
        $this->assertNotNull($response);
        $this->assertNotNull($response->result);
        $this->assertNotNull($response->result->formatted_phone_number);
    	$this->assertEquals($expected, $response->result->formatted_phone_number);
    }

    public function phoneProvider() {
        return [
            ["Avalon Ventures", "(858) 348-2180"],
        ];
    }
}