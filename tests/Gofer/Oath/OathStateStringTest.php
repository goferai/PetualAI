<?php

class OathStateStringTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider constructProvider
     * @param $string
     * @param $expected
     */
	public function test_construct($string, $expected) {
	    $oathStateString = new \Gofer\Oath\OathStateString($string);
        $this->assertEquals($expected, $oathStateString->getString());
	}

    public function constructProvider() {
        return [
            ['abc', 'abc'],
        ];
    }

    /**
     * @dataProvider addProvider
     * @param $string
     * @param $key
     * @param $value
     * @param $expected
     */
    public function test_add($string, $key, $value, $expected) {
        $oathStateString = new \Gofer\Oath\OathStateString($string);
        $oathStateString->add($key, $value);
        $this->assertEquals($expected, $oathStateString->getString());
    }

    public function addProvider() {
        return [
            [null, 'token', 'value', '|token~value~token|'],
            ['|key1~~value1|~key1|', 'token', 'value', '|key1~~value1|~key1token~value~token|'],
        ];
    }

    /**
     * @dataProvider getValueProvider
     * @param $string
     * @param $key
     * @param $expected
     */
    public function test_getValue($string, $key, $expected) {
        $oathStateString = new \Gofer\Oath\OathStateString($string);
        $this->assertEquals($expected, $oathStateString->getValue($key));
    }

    public function getValueProvider() {
        return [
            [null, 'token', ''],
            ['|key1~~value1|~key1|', 'token', ''],
            ['|key1~~value1|~key1|', 'key1', '~value1|'],
            ['|token~value~token|', 'token', 'value'],
            ['|user_id~1~user_idstate~value~state|', 'state', 'value'],
            ['|user_id~1~user_idstate~value~statecode~2342~code|', 'code', '2342'],
        ];
    }

}