<?php

use Gofer\Salesforce\NlpMerger\ReportNlpMerger;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class ReportNlpMergerTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider buildSynonymsProvider
     * @param $name
     * @param $expected
     */
    public function test_buildSynonyms($name, $expected) {
        $privateMethod = TestingUtil::getClassPrivateMethod(ReportNlpMerger::class, 'buildSynonyms');
        $nlpMerger = new ReportNlpMerger(DateUtil::getCurrentDateTimeUTC());
        $result = $privateMethod->invokeArgs($nlpMerger, [$name]);
        $this->assertEquals($expected, $result);
    }

    public function buildSynonymsProvider() {
        return [
            [
                'sales person report',
                [
                    'sales person report',
                    'sales person dashboard',
                ]
            ],
            [
                'sales person',
                [
                    'sales person',
                    'sales person report',
                    'sales person dashboard',
                ]
            ],
            [
                'sales person overview',
                [
                    'sales person overview',
                    'sales person overview report',
                    'sales person overview dashboard',
                ]
            ],
            [
                'accounts',
                [
                    'accounts report',
                    'accounts dashboard',
                ]
            ],
            [
                'Accounts',
                [
                    'accounts report',
                    'accounts dashboard',
                ]
            ],
            [
                'report',
                []
            ],
            [
                'test report',
                []
            ],
            [
                '123',
                []
            ],
            [
                '<Amazing REPORT>',
                []
            ],
        ];
    }
    
}