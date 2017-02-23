<?php

use Gofer\Salesforce\Analytics\ReportExecuteResponse;
use Gofer\Util\Log;

class ReportExecuteResponseTest extends PHPUnit_Framework_TestCase {

    public function test_initializeForData() {
        $log = new Log(basename(__FILE__));
        $reportExecuteResponse = new ReportExecuteResponse();
        $json = file_get_contents(__DIR__.'/test_initializeForData.json');
        $log->debug('json = '.substr($json, 0, 30));
        $reportExecuteResponse->initializeForJSON($json);
        $this->assertEquals(true, $reportExecuteResponse->allData());
        $this->assertEquals(true, $reportExecuteResponse->hasDetailRows());
        $log->debug('json = '.print_r($reportExecuteResponse, true));
        $this->assertEquals(true, $reportExecuteResponse->getFactMap()->hasKey('T!T'));
        $this->assertEquals(true, $reportExecuteResponse->getFactMap()->getDataItem('T!T')->hasRows());
        $dataCell = $reportExecuteResponse->getFactMap()->getDataItem('T!T')->getRows()[0];
        $log->debug('$dataCell = '.print_r($dataCell, true));
        $this->assertEquals('Chip Allen', $reportExecuteResponse->getFactMap()->getDataItem('T!T')->getRows()[0]->getDataCells()[0]->getLabel());
        $log->debug('$reportExecuteResponse->getReportExtendedMetadata()->getDetailColumnInfoAtIndex(0) = '.print_r($reportExecuteResponse->getReportExtendedMetadata()->getDetailColumnInfoAtIndex(0), true));
        $this->assertEquals('Account Owner', $reportExecuteResponse->getReportExtendedMetadata()->getDetailColumnInfoAtIndex(0)->getLabel());
    }

}