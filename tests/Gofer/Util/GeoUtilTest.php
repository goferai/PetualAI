<?php

use Gofer\Util\GeoUtil;

class GeogUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider distanceProvider
     * @param $latitudeA
     * @param $longitudeA
     * @param $latitudeB
     * @param $longitudeB
     * @param $expectedMiles
     */
	public function test_distance($latitudeA, $longitudeA, $latitudeB, $longitudeB, $expectedMiles) {
		$result = GeoUtil::distanceMiles($latitudeA, $longitudeA, $latitudeB, $longitudeB);
        $this->assertEquals($expectedMiles, round($result));
	}

    public function distanceProvider() {
        return [
            [21.1767238, 72.7936959, 21.1753873, 71.7927165, 64],
        ];
    }

    /**
     * @dataProvider isValidLatitudeOrLongitudeProvider
     * @param $value
     * @param $expected
     */
    public function test_isValidLatitudeOrLongitude($value, $expected) {
        $this->assertEquals($expected, GeoUtil::isValidLatitudeOrLongitude($value));
    }

    public function isValidLatitudeOrLongitudeProvider() {
        return [
            ['33', true],
            ['33.3', true],
            ['-33.3', true],
            ['null', false],
            [33.3, true],
            ['a.a', false],
        ];
    }

}