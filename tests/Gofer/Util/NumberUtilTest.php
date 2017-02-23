<?php

use Gofer\Util\NumberUtil;

class NumberUtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider numbsLsTnHundred
     * @param $percent
     * @param $expected
     */
    public function test_convertPercentToRange10($percent, $expected) {
        $result = NumberUtil::convertPercentToRange10($percent);
        $this->assertEquals($expected, $result);
    }

    public function numbsLsTnHundred() {
        return array(
            array(0, 0),
            array(9, 0),
            array(10, 1),
            array(33, 3),
            array(100, 10),

            // Bigger than 100
            array(101, 10),
        );
        // Returns float not int
    }

    /**
     * @dataProvider isIntegerBetweeenProvider
     * @param $values
     * @param $start
     * @param $end
     * @param $expected
     */
    public function test_isIntegerBetween($values, $start, $end, $expected) {
        $result = NumberUtil::isIntegerBetween($values, $start, $end);
        $this->assertEquals($expected, $result);
    }

    public function isIntegerBetweeenProvider() {
        return [
            ['1', 1, 2, true],
            ['2', 1, 2, true],
            ['1.5', 1, 2, false],
            ['0', 1, 2, false],
            [1, 1, 2, true],
            [2, 1, 2, true],
            [2.0, 1, 2, true],
            [0, 1, 2, false],
            [1.5, 1, 2, false],
            ['hello', 1, 2, false],
            [2, -1, 2, true],
            ['-2', -2, -3, false],
            ['-2.0', -2, -3, false],
            ['-1.00', -2, 3, false],
            ['hello', 1, 2, false],
        ];
        // Returns float not int
    }
	
}
