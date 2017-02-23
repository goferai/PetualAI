<?php

use Gofer\Salesforce\Salesforce;
use Gofer\Salesforce\SalesforceObjectTypes;

abstract class SalesforceAction_TestCase extends PHPUnit_Framework_TestCase {
	
	protected static function removeTestDataForQuery($query, $objectType) {
		$salesforce = new Salesforce();
		$records = $salesforce->query($query, $objectType);
		foreach($records as $record) {
			$record->delete();
		}
	}
	
	protected static function removeTestDataForAccountName($name) {
		$salesforce = new Salesforce();
		$records = $salesforce->query("SELECT Id from Account where Name = '$name'", SalesforceObjectTypes::ACCOUNT);
		foreach($records as $record) {
			$record->delete();
		}
	}
	
	protected static function removeTestDataForContactName($name) {
		$salesforce = new Salesforce();
		$records = $salesforce->query("SELECT Id from Contact where Name = '$name'", SalesforceObjectTypes::CONTACT);
		foreach($records as $record) {
			$record->delete();
		}
	}
	
}