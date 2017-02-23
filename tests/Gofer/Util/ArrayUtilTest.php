<?php

use Gofer\Util\ArrayUtil;

class ArrayUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider filterOnlyStringsProvider
     * @param $subject
     * @param $strings
     * @param $expected
     */
    public function test_filterOnlyStrings($subject, $strings, $expected) {
        $result = ArrayUtil::filterOnlyStrings($strings, $subject);
        $this->assertEquals($expected, $result);
    }

    public function filterOnlyStringsProvider() {
        return [
            [['a', 'b', 'c', 'd'], ['a','b', 'd'] ,['a','b', 'd']],
            [['a', 'b', 'c', 'd'], ['a','bb', 'd'] ,['a', 'd']],
            [['a', 'b', 'c', 'd'], ['a','a', 'd'] ,['a', 'd']],
            [[], ['a','c', 'd'] ,[]],
            [['a', 'b', 'c', 'd'], [] , []],
        ];
    }

    /**
     * @dataProvider removeStringsProvider
     * @param $subject
     * @param $strings
     * @param $expected
     */
	public function test_removeStrings($subject, $strings, $expected) {
		$result = ArrayUtil::removeStrings($strings, $subject);
        $this->assertEquals($expected, $result);
	}

    public function removeStringsProvider() {
        return [
            [['a', 'b', 'c', 'd'], ['a','b', 'd'] ,['c']],
            [['a', 'b', 'c', 'd'], ['a','bb', 'd'] ,['b', 'c']],
            [['a', 'b', 'c', 'd'], ['a','a', 'd'] ,['b', 'c']],
            [[], ['a','c', 'd'] ,[]],
            [['a', 'b', 'c', 'd'], [] , ['a', 'b', 'c', 'd']],
        ];
    }

    /**
     * @dataProvider removeStringsWithPrefixProvider
     * @param $subject
     * @param $prefixes
     * @param $expected
     */
    public function test_removeStringsWithPrefix($subject, $prefixes, $expected) {
        $result = ArrayUtil::removeStringsWithPrefix($prefixes, $subject);
        $this->assertEquals($expected, $result);
    }

    public function removeStringsWithPrefixProvider() {
        return [
            [['a', 'b', 'c', 'd'], ['a','b', 'd'] ,['c']],
            [['a', 'b', 'c', 'd'], ['a','bb', 'd'] ,['b', 'c']],
            [['a', 'b', 'c', 'd'], ['a','a', 'd'] ,['b', 'c']],
            [[], ['a','c', 'd'] ,[]],
            [['a', 'b', 'c', 'd'], [] , ['a', 'b', 'c', 'd']],
            [['ab', 'ba', 'cac', 'dad'], ['a'] , ['ba', 'cac', 'dad']],
        ];
    }



    /**
     * @dataProvider addSuffixProvider
     * @param $subject
     * @param $suffix
     * @param $expected
     */
    public function test_addSuffix($subject, $suffix, $expected) {
        $result = ArrayUtil::addSuffix($suffix, $subject);
        $this->assertEquals($expected, $result);
    }

    public function addSuffixProvider() {
        return [
            [['a', 'b', 'c', 'd'], '' ,['a', 'b', 'c', 'd']],
            [['a', 'b', 'c', 'd'], 'z' ,['az', 'bz', 'cz', 'dz']],
            [[], 'z' ,[]],
        ];
    }

}