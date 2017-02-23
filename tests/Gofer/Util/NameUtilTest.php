<?php

class NameUtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider fullNames4First
     * @param $fullName
     * @param $expected
     */
	public function test_getFirstNameFromFullName($fullName, $expected) {
		$result = \Gofer\Util\NameUtil::getFirstNameFromFullName($fullName);
        $this->assertEquals($expected, $result);
	}
	
	public function fullNames4First() {
		return array(
			array('Tom John Smith', 'Tom'),
			array('Tom-John Smith', 'Tom-John'),
			array('Tom  Smith', 'Tom'),
            array(' Tom Smith', 'Tom'),
			array('Tom-John', 'Tom-John'),
			array('', ''),
		);
	}

    /**
     * @dataProvider fullNames4Last
     * @param $fullName
     * @param $expected
     */
	public function test_getLastNameFromFullName($fullName, $expected) {
		$result = \Gofer\Util\NameUtil::getLastNameFromFullName($fullName);
        $this->assertEquals($expected, $result);
	}
	
	public function fullNames4Last() {
		return array(
			array('Tom John Smith', 'John Smith'),
			array('Tom-John Smith', 'Smith'),
			array('Tom  Smith', 'Smith'),
            array(' Tom Smith', 'Smith'),
            array(' Tom Smith ', 'Smith'),
            array('Tom  Smith ', 'Smith'),
			array('Tom-John', ''),
			array('', ''),
		);
	}

    /**
     * @dataProvider names4Valid
     * @param $fullName
     * @param $expected
     */
	public function test_isValidName($fullName, $expected) {
		$result = \Gofer\Util\NameUtil::isValidName($fullName);
        $this->assertSame($expected, $result);
	}
	
	public function names4Valid() {
		return array(
			array('Tom John Smith', TRUE),
			array('Tom-John Smith', TRUE),
			array('Tom  Smith', TRUE),
            array(' Tom Smith', TRUE),
			array('T', FALSE),
			array('TS', TRUE),
			array('Tom@John', FALSE),
			array('', FALSE),
		);
	}
	
}
