<?php


use Gofer\Salesforce\NlpMerger\DashboardNlpMerger;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class DashboardtNlpMergerTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider buildSynonymsProvider
     * @param $name
     * @param $expected
     */
    public function test_buildSynonyms($name, $expected) {
        $privateMethod = TestingUtil::getClassPrivateMethod(DashboardNlpMerger::class, 'buildSynonyms');
        $nlpMerger = new DashboardNlpMerger(DateUtil::getCurrentDateTimeUTC());
        $result = $privateMethod->invokeArgs($nlpMerger, [$name]);
        $this->assertEquals($expected, $result);
    }

    public function buildSynonymsProvider() {
        return [
            [
                'sales person dashboard',
                [
                    'sales person dashboard',
                    'sales person report',
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
                    'accounts dashboard',
                    'accounts report',
                ]
            ],
            [
                'Accounts',
                [
                    'accounts dashboard',
                    'accounts report',
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