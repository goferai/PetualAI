<?php

use Gofer\Salesforce\Objects\Account;
use Gofer\Salesforce\Salesforce;
use Gofer\Salesforce\SalesforceConnection;
use Gofer\Salesforce\SalesforceObjectTypes;
use Gofer\Util\TestingUtil;

class SalesforceAccountTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \Gofer\Salesforce\Objects\Account
     */
	public static $account;

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
			SalesforceConnection::getInstance()->setupConnection(TestingUtil::$mainTestUserID);
			self::$account = new Account();
			self::$account->initializeForID('0013600000HF7yQAAT');
			self::removeTestAccounts();
			self::$setupRan = true;
		}
	}
	
	public function testInitializeForID() {
		$account = new Account();
		$account->initializeForID('0013600000HF7yQAAT');
		$this->assertEquals('Acme (Sample)', $account->Name);
		$this->assertEquals('Account', $account->attributes->type);
	}
	
	public function testConvertSystemDateFieldsToDates() {
		self::$account->convertSystemDateFieldsToDates();
		$expectedObject = new \DateTime();
		$this->assertInstanceOf(get_class($expectedObject), self::$account->CreatedDate);
	}
	
	public function testGetObjectType() {
		$this->assertEquals('Account', self::$account->getObjectType());
	}
	
	public function testGetObjectFields() {
		$this->assertContains('Name', self::$account->getObjectFields());
	}
	
	public function testCreate() {
		$account = new Account();
		$account->Name = 'testCreate';
		$account->BillingCity = 'San Diego';
		$account->create();
		$this->assertObjectHasAttribute('Id', $account);
		$this->assertGreaterThan(8, strlen($account->Id));
	}
	
	public function testUpdate() {
		$account = new Account();
		$account->Name = 'testUpdate';
		$account->BillingCity = 'San Diego';
		$account->create();
		$account->BillingCity = 'San Diego Rules';
		$account->update(array('BillingCity'));
		$this->assertEquals('San Diego Rules', $account->BillingCity);
	}
	
	public function testDelete() {
		$account = new Account();
		$account->Name = 'testDelete';
		$account->create();
		$account->delete();
		$salesforce = new Salesforce();
		$accounts = $salesforce->query("SELECT Id, Name from ACCOUNT where Name = 'testDelete'", SalesforceObjectTypes::ACCOUNT);
		$this->assertCount(0, $accounts);
	}
	
	public function testInitializeForName() {
		$account = new Account();
		$account->initializeForName('Acme (Sample)');
		$this->assertEquals('Acme (Sample)', $account->Name);
		$this->assertEquals('Account', $account->attributes->type);
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