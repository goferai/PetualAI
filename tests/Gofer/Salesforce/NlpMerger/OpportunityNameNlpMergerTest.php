<?php

use Gofer\Salesforce\NlpMerger\OpportunityNameNlpMerger;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class OpportunityNameNlpMergerTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider buildSynonymsProvider
     * @param $name
     * @param $expected
     */
    public function test_buildSynonyms($name, $expected) {
        $privateMethod = TestingUtil::getClassPrivateMethod(OpportunityNameNlpMerger::class, 'buildSynonyms');
        $nlpMerger = new OpportunityNameNlpMerger(DateUtil::getCurrentDateTimeUTC());
        $result = $privateMethod->invokeArgs($nlpMerger, [$name]);
        $this->assertEquals($expected, $result);
    }

    public function buildSynonymsProvider() {
        return [
            [
                'something opportunity',
                [
                    'something opportunity',
                    'something',
                ]
            ],
            [
                'something',
                [
                    'something',
                    'something opportunity',
                ]
            ],
            [
                'account',
                []
            ],
            [
                'reports',
                []
            ],
            [
                '',
                []
            ],
            [
                'test',
                []
            ],
            [
                '"><',
                []
            ],
            [
                'test\'"><img src=x onerror=alert(23)>',
                []
            ],
        ];
    }
    
}