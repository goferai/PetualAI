<?php

use Gofer\Salesforce\Analytics\ReportToStringConverter;
use Gofer\Salesforce\Salesforce;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Util\TestingUtil;

class ReportToStringConverterTest extends PHPUnit_Framework_TestCase {

    /**
     * @var bool
     */
    public static $setupRan = false;

    /**
     * @var bool
     */
    public static $cleanupRan = false;

    public static function setupBeforeClass() {
        if (!self::$setupRan) {
            SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestAdminUserID);
            self::$setupRan = true;
        }
    }

    public function test_convert() {
        $reportId = '00O36000004IUWCEA4';
        $salesforce = new Salesforce();
        $reportExecuteResponse = $salesforce->runReport($reportId);
        $reportToStringConverter = new ReportToStringConverter($reportExecuteResponse);
        $this->assertEquals(true, $reportToStringConverter->canBeConverted());
        $this->assertGreaterThan(100, strlen($reportToStringConverter->convert()));
    }

}