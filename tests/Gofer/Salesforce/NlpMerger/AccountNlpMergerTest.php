<?php


use Gofer\Salesforce\NlpMerger\AccountNlpMerger;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class AccountNlpMergerTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider tryGetEntryProvider
     * @param $entity
     * @param $name
     * @param $synonyms
     * @param $newValuesExpected
     * @param $expected
     */
    public function test_tryGetEntry($entity, $name, $synonyms, $newValuesExpected, $expected) {
        $privateMethod = TestingUtil::getClassPrivateMethod(AccountNlpMerger::class, 'tryGetEntry');
        $accountNlpMerger = new AccountNlpMerger(DateUtil::getCurrentDateTimeUTC());
        $newValues = false;
        $result = $privateMethod->invokeArgs($accountNlpMerger, [$entity, $name, $synonyms, &$newValues]);
        $this->assertEquals($newValuesExpected, $newValues);
        $this->assertEquals($expected, $result);
    }

    public function tryGetEntryProvider() {
        $entity = new \Gofer\ApiAi\Resources\ApiAiEntity();
        $entity->initializeForJSON('
            {
              "id": "e116be1d-f47f-44b4-aa31-24d92b5a6e88",
              "name": "company_name",
              "isOverridable": true,
              "entries": [
                {
                  "value": "edge communications",
                  "synonyms": [
                    "edge communications",
                    "Edge Communications"
                  ]
                }],
              "isEnum": false,
              "automatedExpansion": true
            }');

        $entry1 = new \Gofer\ApiAi\Resources\ApiAiEntityEntry();
        $synonyms1 = ['edge communications', 'Edge Communications'];
        $entry1->setValue('edge communications')->setSynonyms($synonyms1);

        $entry2 = new \Gofer\ApiAi\Resources\ApiAiEntityEntry();
        $synonyms2 = ['edge communications, corp', 'edge communications'];
        $entry2->setValue('edge communications corp')->setSynonyms($synonyms2);
        return [
            [$entity, 'edge_communications_', $synonyms1, false, $entry1],
            [$entity, 'edge_communications, corp', $synonyms2, true, $entry2],
        ];
    }

    /**
     * @dataProvider buildSynonymsProvider
     * @param $name
     * @param $expected
     */
    public function test_buildSynonyms($name, $expected) {
        $privateMethod = TestingUtil::getClassPrivateMethod(AccountNlpMerger::class, 'buildSynonyms');
        $accountNlpMerger = new AccountNlpMerger(DateUtil::getCurrentDateTimeUTC());
        $result = $privateMethod->invokeArgs($accountNlpMerger, [$name]);
        $this->assertEquals($expected, $result);
    }

    public function buildSynonymsProvider() {
        return [
            [
                'edge_communications_',
                [
                    'edge communications',
                ]
            ],
            [
                'edge_communications, corp',
                [
                    'edge communications corp',
                    'edge communications, corp',
                    'edge communications',
                ]
            ],
            [
                'edge_communications, llc',
                [
                    'edge communications llc',
                    'edge communications, llc',
                    'edge communications',
                ]
            ],
            [
                'edge_communications corporation',
                [
                    'edge communications corporation',
                    'edge communications',
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