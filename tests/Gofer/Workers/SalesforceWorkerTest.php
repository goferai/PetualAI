<?php

use Gofer\ApiAi\ApiAiClient;
use Gofer\Exceptions\AppNotActivatedException;
use Gofer\SDK\Models\Entities\CompanyNameEntity;
use Gofer\SDK\Models\Entities\ContactFirstNameEnumEntity;
use Gofer\SDK\Models\Entities\ContactLastNameEnumEntity;
use Gofer\SDK\Models\Entities\DashboardEntity;
use Gofer\SDK\Models\Entities\OpportunityNameEntity;
use Gofer\SDK\Models\Entities\ReportEntity;
use Gofer\SDK\Services\SfdcOrganizationService;
use Gofer\SDK\Services\SfdcOrganizationServiceOptions;
use Gofer\SDK\Services\UserAppService;
use Gofer\SDK\Services\UserAppServiceOptions;
use Gofer\Util\DateUtil;
use Gofer\Util\EmailUtil;
use Gofer\Util\TestingUtil;
use Gofer\Workers\SalesforceWorker;

class SalesforceWorkerTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        TestingUtil::emptyTables([
            'sfdc_accounts',
            'sfdc_reports',
            'sfdc_dashboards',
            'sfdc_opportunities'
        ]);

        $apiAiClient = new ApiAiClient(APIAI_DEVELOPER_ACCESS_TOKEN);
        $apiAiClient->deleteEntityEntries(CompanyNameEntity::ENTITY_NAME, ['Moes Bagels']);
        $apiAiClient->deleteEntityEntries(ContactFirstNameEnumEntity::ENTITY_NAME, ['jack']);
        $apiAiClient->deleteEntityEntries(ContactLastNameEnumEntity::ENTITY_NAME, ['smith']);
    }

    public function test_runSalesforceWorkerTest() {
        EmailUtil::$fakeSendEmailsForTesting = true;
        $userAppService = new UserAppService();
        $userApp = $userAppService->get((new UserAppServiceOptions())->setUserId(TestingUtil::$mainTestUserID)->setAppId(2));
        if(!$userApp) throw new AppNotActivatedException();

        $sfdcOrganizationService = new SfdcOrganizationService();
        $sfdcOrganization = $sfdcOrganizationService->get((new SfdcOrganizationServiceOptions())->setOrganizationId($userApp->getOrganizationId()));
        if ($sfdcOrganization) {
            $sfdcOrganization->setLastDateSourced((new DateTime('1984-06-25'))->format(DateUtil::FORMAT_ISO8601));
            $sfdcOrganizationService->upsert($sfdcOrganization);
        }

        $worker = new SalesforceWorker();
        $result = $worker->run($userApp);
        
        $row = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_accounts');
        $this->assertNotEquals(false, $row);
        $this->assertGreaterThanOrEqual(1, intval($row->cnt));

//        $row2 = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_contacts');
//        $this->assertNotEquals(false, $row2);
//        $this->assertEquals(intval($row2->cnt), intval($result['contacts']));

        $row3 = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_reports');
        $this->assertNotEquals(false, $row3);
        $this->assertGreaterThanOrEqual(1, intval($row3->cnt));

        $row4 = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_dashboards');
        $this->assertNotEquals(false, $row4);
        $this->assertGreaterThanOrEqual(1, intval($row4->cnt));

        $row6 = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_opportunities');
        $this->assertNotEquals(false, $row6);
        $this->assertGreaterThanOrEqual(1, intval($row6->cnt));

        $combined = intval($row->cnt) + intval($row3->cnt) + intval($row4->cnt) + intval($row6->cnt);
        $this->assertEquals($combined, intval($result[0]));

//        $row7 = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_tasks');
//        $this->assertNotEquals(false, $row7);
//        $this->assertEquals(intval($row7->cnt), intval($result['tasks']));

        $sfdcOrganization2 = $sfdcOrganizationService->get((new SfdcOrganizationServiceOptions())->setOrganizationId($userApp->getOrganizationId()));
        $this->assertNotNull($sfdcOrganization2);
        $fiveMinutesAgo = new DateTime();
        $fiveMinutesAgo->modify('-5 minutes');
        $this->assertGreaterThan($fiveMinutesAgo, new DateTime($sfdcOrganization2->getLastDateSourced()));

        $apiAiClient = new ApiAiClient(APIAI_DEVELOPER_ACCESS_TOKEN);
        $entity = $apiAiClient->getEntity(CompanyNameEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity->getIndexOfEntryName('Moes Bagels'));

        $entity2 = $apiAiClient->getEntity(ReportEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity2->getIndexOfEntryName('Sales Person MTD Sales'));
        $this->assertGreaterThanOrEqual(0, $entity2->getIndexOfEntryName('Sales Person MTD Sales Report'));

        $entity3 = $apiAiClient->getEntity(DashboardEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity3->getIndexOfEntryName('Agent Supervisor Overview'));
        $this->assertGreaterThanOrEqual(0, $entity3->getIndexOfEntryName('Agent Supervisor Overview Dashboard'));

        $entity4 = $apiAiClient->getEntity(OpportunityNameEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity4->getIndexOfEntryName('United Oil Plant Standby Generators'));
        $this->assertGreaterThanOrEqual(0, $entity4->getIndexOfEntryName('United Oil Plant Standby Generators Opportunity'));

        $entity = $apiAiClient->getEntity(ContactFirstNameEnumEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity->getIndexOfEntryName('jack'));

        $entity = $apiAiClient->getEntity(ContactLastNameEnumEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity->getIndexOfEntryName('smith'));
    }
    
}