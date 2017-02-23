<?php

use Gofer\Salesforce\SalesforceConnection;
use Gofer\Salesforce\Salesforce;
use Gofer\Salesforce\SalesforceObjectTypes;
use Gofer\Salesforce\Objects\Account;
use Gofer\Util\ObjectUtil;
use Gofer\Salesforce\BulkApiObjects\Operations;
use Gofer\Salesforce\BulkApiObjects\ContentTypes;
use Gofer\Util\TestingUtil;

class SalesforceSDKTest extends PHPUnit_Framework_TestCase {
	
	public static $setupRan = false;
	public static $cleanupRan = false;
	
	public static function setupBeforeClass() {
		if (!self::$setupRan) {
            SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
			self::removeTestAccounts();
			self::$setupRan = true;
		}
	}
	
	public function testSearch() {
		$salesforce = new Salesforce();
		$accounts = $salesforce->search("FIND {acme} RETURNING Account(Id,Name)", SalesforceObjectTypes::ACCOUNT);
		$this->assertGreaterThan(0, count($accounts));
		$account = $accounts[0];
		$expectedObject = new Account();
		$this->assertInstanceOf(get_class($expectedObject), $account);
	}
	
	public function testSearch2() {
		$salesforce = new Salesforce();
		$accounts = $salesforce->search("FIND {acme} RETURNING Account(Id,Name)");
		$this->assertGreaterThan(0, count($accounts));
		$account = $accounts[0];
		$expectedObject = new \stdClass();
		$this->assertInstanceOf(get_class($expectedObject), $account);
	}
	
	/**
	 * @expectedException Gofer\Exceptions\AppNotConnectingException
	 */
	public function testSearch_Error() {
		$salesforce = new Salesforce();
		$salesforce->search("FIND {acme} RETURNING BADQUERY", SalesforceObjectTypes::ACCOUNT);
	}
	
	/**
	 * @expectedException Gofer\Exceptions\AppNotConnectingException
	 */
	public function testSearch_Error2() {
		$salesforce = new Salesforce();
		$salesforce->search("", SalesforceObjectTypes::ACCOUNT);
	}
	
	public function testQuery() {
		$salesforce = new Salesforce();
		$accounts = $salesforce->query("SELECT Id, Name from ACCOUNT where Name like 'Acme%'", SalesforceObjectTypes::ACCOUNT);
		$this->assertGreaterThan(0, count($accounts));
		$account = $accounts[0];
		$expectedObject = new Account();
		$this->assertInstanceOf(get_class($expectedObject), $account);
	}
	
	public function testQuery2() {
		$salesforce = new Salesforce();
		$accounts = $salesforce->query("SELECT Id, Name from ACCOUNT where Name like 'Acme%'");
		$this->assertGreaterThan(0, count($accounts));
		$account = $accounts[0];
		$expectedObject = new \stdClass();
		$this->assertInstanceOf(get_class($expectedObject), $account);
	}
	
	public function testGet() {
		$salesforce = new Salesforce();
		$account = $salesforce->get(SalesforceObjectTypes::ACCOUNT, '0013600000HF7yQAAT');
		$expectedObject = new Account();
		$this->assertInstanceOf(get_class($expectedObject), $account);
		$this->assertEquals('Acme (Sample)', $account->Name);
	}
	
	public function testGet_Fields() {
		$salesforce = new Salesforce();
		$account = $salesforce->get(SalesforceObjectTypes::ACCOUNT, '0013600000HF7yQAAT', array('Id', 'Name'));
		$expectedObject = new Account();
		$this->assertInstanceOf(get_class($expectedObject), $account);
		$this->assertEquals('Acme (Sample)', $account->Name);
	}
	
	public function testGet_StdClass() {
		$salesforce = new Salesforce();
		$account = $salesforce->get(SalesforceObjectTypes::ACCOUNT, '0013600000HF7yQAAT', array('Id', 'Name'), true);
		$expectedObject = new \stdClass();
		$this->assertInstanceOf(get_class($expectedObject), $account);
		$this->assertEquals('Acme (Sample)', $account->Name);
	}
	
	public function testCreate() {
		$salesforce = new Salesforce();
		$data = new \stdClass();
		$data->Name = 'testCreate';
		$data->BillingCity = 'San Diego';
		$account = $salesforce->create(SalesforceObjectTypes::ACCOUNT, $data);
		$expectedObject = new \stdClass();
		$this->assertInstanceOf(get_class($expectedObject), $account);
		$this->assertObjectHasAttribute('Id', $account);
		$this->assertEquals('San Diego', $account->BillingCity);
	}
	
	public function testUpdate() {
		$salesforce = new Salesforce();
		$data = new \stdClass();
		$data->Name = 'testUpdate';
		$data->BillingCity = 'San Diego';
		$account = $salesforce->create(SalesforceObjectTypes::ACCOUNT, $data);
		$account->BillingCity = 'San Diego Rules';
		$strippedAccount = ObjectUtil::getClonedObjectWithProperties($account, array('BillingCity'));
		$salesforce->update(SalesforceObjectTypes::ACCOUNT, $account->Id, $strippedAccount);
		$accounts = $salesforce->query("SELECT Id, BillingCity from ACCOUNT where Name like 'testUpdate%'", SalesforceObjectTypes::ACCOUNT);
		$this->assertCount(1, $accounts);
		$this->assertEquals('San Diego Rules', $accounts[0]->BillingCity);
	}
	
	public function testBatchQuery_SingleAccount() {
		$salesforce = new Salesforce();
		$jobInfo = $salesforce->createJob(Operations::QUERY, SalesforceObjectTypes::ACCOUNT, ContentTypes::JSON);
		$batchInfo = $salesforce->addBatchQueryToJob($jobInfo->id, "select Id, Name from Account where Id = '0013600000HF7yVAAT'");
		
		$timeoutDate = new \DateTime();
		$timeoutDate->modify('+10 minutes');
		$waitTillDone = true;
		$timeoutOccurred = false;
		while ($waitTillDone) {
			sleep(1);
			$jobStatusInfo = $salesforce->checkStatusForJob($jobInfo->id);
			if ($jobStatusInfo->isJobComplete()) {
				$waitTillDone = false;
			}
			$now = new \DateTime();
			if ($now > $timeoutDate) {
				$waitTillDone = false;
				$timeoutOccurred = true;
			}
		}
		$account = new Account();
		$this->assertEquals(false, $timeoutOccurred);
		if (!$timeoutOccurred) {
			$batchIDs = $salesforce->getBatchResultIDs($jobInfo->id, $batchInfo->id);
			$this->assertInternalType('array', $batchIDs);
			$this->assertCount(1, $batchIDs);
			$resultJSON = $salesforce->getBatchResults($jobInfo->id, $batchInfo->id, $batchIDs[0]);
			$result = json_decode($resultJSON);
			$this->assertInternalType('array', $result);
			$this->assertCount(1, $result);
			$account->initializePropertiesForObject($result[0]);
			$this->assertEquals('Global Media (Sample)', $account->Name);
		}
		
	}
	
	public static function tearDownAfterClass() {
		if (!self::$cleanupRan) {
			self::removeTestAccounts();
			self::$cleanupRan = true;
		}
	}
    
	private static function removeTestAccounts() {
		$salesforce = new Salesforce();
		$accounts = $salesforce->query("SELECT Id, Name from ACCOUNT where Name = 'testCreate'", SalesforceObjectTypes::ACCOUNT);
		foreach($accounts as $account) {
			$account->delete();
		}
		
		$accounts = $salesforce->query("SELECT Id, Name from ACCOUNT where Name = 'testUpdate'", SalesforceObjectTypes::ACCOUNT);
		foreach($accounts as $account) {
			$account->delete();
		}
	}
	
}