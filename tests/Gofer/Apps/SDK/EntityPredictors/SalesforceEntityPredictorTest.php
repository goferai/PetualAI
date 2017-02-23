<?php

use Gofer\Apps\Salesforce\SalesforceOpenReportAction;
use Gofer\Apps\SDK\EntityPredictors\SalesforceEntityPredictor;
use Gofer\Salesforce\SOSLBuilder;
use Gofer\SDK\Models\Entities\CompanyNameEntity;
use Gofer\SDK\Models\Entities\ContactEntity;
use Gofer\SDK\Models\Entities\ReportEntity;
use Gofer\SDK\Models\Intents\SalesforceOpenContactIntent;
use Gofer\SDK\Models\Intents\SalesforceOpenReportIntent;
use Gofer\Util\TestingUtil;

class SalesforceEntityPredictorTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider searchProvider
     * @param $searchValue
     * @param $action
     * @param $entityId
     * @param $intentId
     * @param $soslBuilder
     * @param $stopWords
     * @param $exitEarly
     */
    public function test_search($searchValue, $action, $entityId, $intentId, $soslBuilder, $stopWords, $exitEarly) {
        $search = TestingUtil::getClassPrivateMethod(SalesforceEntityPredictor::class, 'search');
        $salesforceEntityPredictor = new SalesforceEntityPredictor($action, $searchValue, $entityId, $intentId);
        $salesforceEntityPredictor->setSoslBuilder($soslBuilder)->setStopWords($stopWords);
        $search->invokeArgs($salesforceEntityPredictor, []);
        $this->assertEquals($exitEarly, $salesforceEntityPredictor->isExitEarly());
    }

    public function searchProvider() {
        $soslBuilder = new SOSLBuilder();
        $soslBuilder
            ->setIn(SOSLBuilder::IN_OPTION_NAME)
            ->addReturning("Report(Id, Name)")
            ->addReturning('Dashboard(Id, Title)')
            ->limit(20);
        return [
            [
                'Sales manager dashboard',
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestUserID, [ReportEntity::ENTITY_NAME => 'Sales manager dashboard'], 'Open the sales manager dashboard')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                [],
                false,
            ],
            [
                'zzz dashboard',
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestUserID, [ReportEntity::ENTITY_NAME => 'zzz dashboard'], 'Open the zzz dashboard')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['dashboard'],
                true,
            ],
            [
                'da sales dashboard',
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestUserID, [ReportEntity::ENTITY_NAME => 'da sales dashboard'], 'Open the da sales dashboard')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['dashboard'],
                false,
            ],
        ];
    }

    /**
     * @dataProvider predictProvider
     * @param $searchValue
     * @param $searchNetSize
     * @param \Gofer\Apps\Salesforce\SalesforceAction $action
     * @param $entityId
     * @param $intentId
     * @param $soslBuilder
     * @param $stopWords
     * @param $foundResult
     * @param $expectedId
     * @param $expectedCount
     */
    public function test_predict($searchValue, $searchNetSize, $action, $entityId, $intentId, $soslBuilder, $stopWords, $foundResult, $expectedId, $expectedCount) {
        $salesforceEntityPredictor = new SalesforceEntityPredictor($action, $searchValue, $entityId, $intentId);
        $salesforceEntityPredictor->setSoslBuilder($soslBuilder)
                                  ->setStopWords($stopWords)
                                  ->setSearchNetSize($searchNetSize)
                                  ->predict();
        $this->assertEquals($foundResult, $salesforceEntityPredictor->foundResult());
        if ($foundResult) {
            $this->assertEquals($expectedId, $salesforceEntityPredictor->getPrediction()->getAppDefinedId());
        }
        $row = TestingUtil::getSingleRow("SELECT count(*) as cnt FROM gofer.srvc_message_entity_matches	where message_id = '{$action->getMessage()->getMessageId()}'");
        $this->assertEquals($expectedCount, $row->cnt);
    }

    public function predictProvider() {
        $soslBuilder = new SOSLBuilder();
        $soslBuilder
            ->setIn(SOSLBuilder::IN_OPTION_NAME)
            ->addReturning("Report(Id, Name)")
            ->addReturning('Dashboard(Id, Title)');
        $soslBuilderWithWhere = new SOSLBuilder();
        $soslBuilderWithWhere
            ->setIn(SOSLBuilder::IN_OPTION_NAME)
            ->addReturning("Contact(Id, Name WHERE Account.Name like '%microsoft%' )");
        return [
            // find a specific report with strict net utilizing stop words to be removed
            [
                'person mtd report',
                3,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ReportEntity::ENTITY_NAME => 'Person MTD report'], 'Open the Person MTD report')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['dashboard', 'report'],
                true,
                '00O36000004IUo3EAG',
                0
            ],
            // find many reports (wide net cause terms are off and this has not been chosen before by user)
            [
                'sales persons mtd report',
                3,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ReportEntity::ENTITY_NAME => 'Sales Persons MTD report'], 'Open the Sales Persons MTD report')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['dashboard', 'report'],
                false,
                null,
                5
            ],
            // find a specific report with specific phrase
            [
                'Sales Manager Pipeline Report',
                3,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ReportEntity::ENTITY_NAME => 'Sales Manager Pipeline Report'], 'Open the Sales Manager Pipeline Report')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['dashboard', 'report'],
                true,
                '00O36000004IUW2EAO',
                0
            ],
            // find a specific report with wide net but it has been chosen before by the user
            [
                'Sales Manager Report',
                3,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ReportEntity::ENTITY_NAME => 'Sales Manager Report'], 'Open the Sales Manager Report')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['report'],
                true,
                '01Z36000000Fg3rEAC',
                0
            ],
            // respond about multiple
            [
                'Sales exec Report',
                3,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ReportEntity::ENTITY_NAME => 'Sales exec Report'], 'Open the Sales exec Report')),
                ReportEntity::ENTITY_ID,
                SalesforceOpenReportIntent::INTENT_ID,
                $soslBuilder,
                ['report'],
                false,
                null,
                5
            ],
            // lookup a contact by first name only with a specific account
            [
                'henry',
                1,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ContactEntity::ENTITY_NAME => 'henry', CompanyNameEntity::ENTITY_NAME => 'microsoft'], 'Open henry smith at microsoft')),
                ContactEntity::ENTITY_ID,
                SalesforceOpenContactIntent::INTENT_ID,
                $soslBuilderWithWhere,
                ['contact'],
                true,
                '0033600000H99PDAAZ',
                0
            ],
            // lookup a contact that does not exist with account - no results expected
            [
                'john smith',
                1,
                new SalesforceOpenReportAction(TestingUtil::buildMessage(TestingUtil::$mainTestAdminUserID, [ContactEntity::ENTITY_NAME => 'john smith', CompanyNameEntity::ENTITY_NAME => 'microsoft'], 'Open john smith at microsoft')),
                ContactEntity::ENTITY_ID,
                SalesforceOpenContactIntent::INTENT_ID,
                $soslBuilderWithWhere,
                ['contact'],
                false,
                null,
                0
            ],
        ];
    }

}