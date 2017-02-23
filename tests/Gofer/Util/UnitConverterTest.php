<?php

use Gofer\Util\GeoUtil;
use Gofer\Util\UnitConverter;

class UnitConverterTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider convertToProvider
     * @param $fromUnit
     * @param $toUnit
     * @param $value
     * @param $expected
     */
	public function test_convertTo($fromUnit, $toUnit, $value, $expected) {
	    $unitConverter = new UnitConverter($value, $fromUnit);
		$result = $unitConverter->to($toUnit);
        $this->assertEquals($expected, $result);
	}

    public function convertToProvider() {
        return [
            [GeoUtil::FEET, GeoUtil::MILES, 5280, 1],
            [GeoUtil::FEET, GeoUtil::MILES, 2640, 0.5],
            [GeoUtil::FEET, GeoUtil::MILES, 1320, 0.25],
        ];
    }

}