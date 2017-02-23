<?php

use Gofer\Util\GeoUtil;
use Gofer\Wit\WitDistanceUnits;

class WitDistanceUnitsTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider convertProvider
     * @param $unit
     * @param $expected
     */
    public function test_convertWitDistanceToGoferDistance($unit, $expected) {
    	$result = WitDistanceUnits::convertWitDistanceToGoferDistance($unit);
    	$this->assertEquals($expected, $result);
    }

    public function convertProvider() {
        return [
            [WitDistanceUnits::MILES, GeoUtil::MILES],
            [WitDistanceUnits::FEET, GeoUtil::FEET],
        ];
    }

}