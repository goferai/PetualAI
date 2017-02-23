<?php
//TODO: P1 - Get working again
//
//use Gofer\Util\TestingUtil;
//
//class ExpressionTest extends PHPUnit_Framework_TestCase {
//
//	protected $preserveGlobalState = FALSE;
//	protected $runTestInSeparateProcess = TRUE;
//
//	public function testBuildIntentExpressionVariations() {
//		$user = \Gofer\Util\TestingUtil::getUser(\Gofer\Util\TestingUtil::$mainTestAdminEmail);
//		$inputObject = new stdClass();
//		$inputObject->intentID = 1;
//
//		$inputObject->templates = array('testBuildIntentExpressionVariations <replace1> and <replace2>');
//		$replace1 = new \stdClass();
//		$replace1->key = '<replace1>';
//		$replace1->wit_id = '1';
//		$replace1->values = array((object) ['textValue'=>'a', 'witValue'=>'a']);
//		$replace2 = new \stdClass();
//		$replace2->key = '<replace2>';
//		$replace2->wit_id = '2';
//		$replace2->values = array((object) ['textValue'=>'b', 'witValue'=>'b']);
//		$inputObject->substitutions = array($replace1, $replace2);
//		\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = json_encode($inputObject);
//    	$this->expectOutputRegex('/.*"result":true.*/');
//    	\Gofer\Util\TestingUtil::mockRouterCall($user, 'post', '/api/expressions/buildVariations');
//    }
//
//    public function testBuildIntentExpressionVariations_2() {
//    	$user = \Gofer\Util\TestingUtil::getUser(\Gofer\Util\TestingUtil::$mainTestAdminEmail);
//    	$inputObject = '{
//	"intentID": "34",
//	"templates": ["<MEETING TYPE> testBuildIntentExpressionVariations_2",
//    			"<MEETING TYPE> testBuildIntentExpressionVariations_2 <DATETIME>"
//	],
//	"substitutions": [{
//		"key": "<DATETIME>",
//		"wit_id": "535a810f-8911-4bfe-976a-6e3c193f1251",
//		"values": [{
//			"textValue": "Monday",
//			"witValue": "2016-04-18T00:00:00.000-07:00"
//		}]
//	}, {
//		"key": "<CONTACT>",
//		"wit_id": "535a8111-9b01-4678-80ae-53a22ef0bf5b",
//		"values": [{
//			"textValue": "John",
//			"witValue": "John"
//		}]
//	}, {
//		"key": "<MEETING TYPE>",
//		"wit_id": "56c8e455-0c2e-4c04-9a5b-93cdfc5760a6",
//		"values": [{
//			"textValue": "conference call",
//			"witValue": "conference call"
//		}]
//	}, {
//		"key": "<PLACE>",
//		"wit_id": "56c8e455-0c2e-4c04-9a5b-93cdfc5760a6",
//		"values": [{
//			"textValue": "the office",
//			"witValue": "office"
//		}]
//	}]
//}';
//    	\Gofer\Util\WebServiceUtil::$testingSimulatedRequestBody = $inputObject;
//    	$this->expectOutputRegex('/.*"result":true.*/');
//    	\Gofer\Util\TestingUtil::mockRouterCall($user, 'post', '/api/expressions/buildVariations');
//    	$intentExpression = TestingUtil::getSingleRow("select *
//				from ".MYSQL_DBNAME.".srvc_intent_expressions
//				where expression_text like '%testBuildIntentExpressionVariations_2%' and like '%<%' limit 1", '\Gofer\SDK\IntentExpression');
//    	$this->assertFalse($intentExpression);
//    }
//
//    public static function tearDownAfterClass() {
//    	static::removeTestData();
//    }
//
//    private static function removeTestData() {
//    	TestingUtil::executeSQL("delete from ".MYSQL_DBNAME.".srvc_intent_expressions
//				where expression_text like '%testBuildIntentExpressionVariations%'");
//    }
//
//}