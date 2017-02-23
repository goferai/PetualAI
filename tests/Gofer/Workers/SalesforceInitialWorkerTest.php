<?php

use Gofer\ApiAi\ApiAiClient;
use Gofer\Exceptions\AppNotActivatedException;
use Gofer\SDK\Models\Apps\SalesforceApp;
use Gofer\SDK\Models\Entities\CompanyNameEntity;
use Gofer\SDK\Services\SfdcOrganizationService;
use Gofer\SDK\Services\SfdcOrganizationServiceOptions;
use Gofer\SDK\Services\UserAppInfo;
use Gofer\SDK\Services\UserAppInfoBuilder;
use Gofer\SDK\Services\UserAppInfoService;
use Gofer\SDK\Services\UserAppInfoServiceOptions;
use Gofer\SDK\Services\UserAppService;
use Gofer\SDK\Services\UserAppServiceOptions;
use Gofer\Util\DateUtil;
use Gofer\Util\EmailUtil;
use Gofer\Util\TestingUtil;
use Gofer\Workers\SalesforceInitialWorker;

class SalesforceInitialWorkerTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        TestingUtil::emptyTables(array('sfdc_accounts'));

        $apiAiClient = new ApiAiClient(APIAI_DEVELOPER_ACCESS_TOKEN);
        $apiAiClient->deleteEntityEntries(CompanyNameEntity::ENTITY_NAME, ['Moes Bagels']);

        $userAppInfo = (new UserAppInfoBuilder())->setAppId(SalesforceApp::APP_ID)
                                                 ->setUserId(TestingUtil::$mainTestUserID)
                                                 ->setInfoKey(UserAppInfo::KEY_INITIAL_SALESFORCE_EXPORT_NEEDED)
                                                 ->setInfoValue('Y')
                                                 ->build();
        (new UserAppInfoService())->upsert($userAppInfo);
    }

    public function test_runSalesforceInitialWorker() {
        EmailUtil::$fakeSendEmailsForTesting = true;
        $userAppService = new UserAppService();
        $userApp = $userAppService->get((new UserAppServiceOptions())->setUserId(TestingUtil::$mainTestUserID)->setAppId(SalesforceApp::APP_ID));
        if(!$userApp) throw new AppNotActivatedException();

        $sfdcOrganizationService = new SfdcOrganizationService();
        $sfdcOrganization = $sfdcOrganizationService->get((new SfdcOrganizationServiceOptions())->setOrganizationId($userApp->getOrganizationId()));
        if ($sfdcOrganization) {
            $sfdcOrganization->setLastDateSourced((new DateTime('1984-06-25'))->format(DateUtil::FORMAT_ISO8601));
            $sfdcOrganizationService->upsert($sfdcOrganization);
        }

        $worker = new SalesforceInitialWorker();
        $result = $worker->run();
        
        $row = TestingUtil::getSingleRow('select count(*) as cnt from gofer.sfdc_accounts');
        $this->assertNotEquals(false, $row);
        $this->assertGreaterThanOrEqual(intval($row->cnt), intval($result[0]));

        $sfdcOrganization2 = $sfdcOrganizationService->get((new SfdcOrganizationServiceOptions())->setOrganizationId($userApp->getOrganizationId()));
        $this->assertNotNull($sfdcOrganization2);
        $fiveMinutesAgo = new DateTime();
        $fiveMinutesAgo->modify('-5 minutes');
        $this->assertGreaterThan($fiveMinutesAgo, new DateTime($sfdcOrganization2->getLastDateSourced()));

        $apiAiClient = new ApiAiClient(APIAI_DEVELOPER_ACCESS_TOKEN);
        $entity = $apiAiClient->getEntity(CompanyNameEntity::ENTITY_NAME);
        $this->assertGreaterThanOrEqual(0, $entity->getIndexOfEntryName('Moes Bagels'));

        $userAppInfo = (new UserAppInfoService())->get((new UserAppInfoServiceOptions())->setUserId(TestingUtil::$mainTestUserID)
                                                                                        ->setAppId(SalesforceApp::APP_ID)
                                                                                        ->setInfoKey(UserAppInfo::KEY_INITIAL_SALESFORCE_EXPORT_NEEDED));
        $this->assertNotFalse($userAppInfo);
        $this->assertEquals('N', $userAppInfo->getInfoValue());
    }
    
}