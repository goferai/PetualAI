<?php

use Gofer\Salesforce\NlpMerger\ContactFirstNameNlpMerger;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class ContactFirstNameNlpMergerTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider shouldSkipValueProvider
     * @param $name
     * @param $expected
     */
    public function test_shouldSkipValue($name, $expected) {
        $privateMethod = TestingUtil::getClassPrivateMethod(ContactFirstNameNlpMerger::class, 'shouldSkipValue');
        $nlpMerger = new ContactFirstNameNlpMerger(DateUtil::getCurrentDateTimeUTC());
        $result = $privateMethod->invokeArgs($nlpMerger, [$name]);
        $this->assertEquals($expected, $result);
    }

    public function shouldSkipValueProvider() {
        return [
            ['bob', false],
            ['mary joe', false],
            ['demo', true],
            ['[demo]', true],
            [', llc.', true],
        ];
    }
    
}