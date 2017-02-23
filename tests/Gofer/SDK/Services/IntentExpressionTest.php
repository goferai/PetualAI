<?php

use Gofer\SDK\Services\IntentExpression;
use Gofer\SDK\Services\IntentExpressionService;
use Gofer\Util\TestingUtil;

class IntentExpressionTest extends PHPUnit_Framework_TestCase {
	
	public static function setupBeforeClass() {
		static::removeTestData();
	}
	
	public function testInsertNewIntentExpressionsForTemplates_None() {
		$intentID = 1;
		$templates = array('testInsertNewIntentExpressionsForTemplates_None');
		$substitutions = array();
        $intentExpressionService = new IntentExpressionService();
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
		$intentExpression = TestingUtil::getSingleRow("select * 
				from ".MYSQL_DBNAME.".srvc_intent_expressions 
				where expression_text = 'testInsertNewIntentExpressionsForTemplates_None'", IntentExpression::class);
		$this->assertNotFalse($intentExpression);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_None', $intentExpression->expression_text);
		$this->assertEquals('[]', $intentExpression->entity_json);
	}
	
	public function testInsertNewIntentExpressionsForTemplates_Single() {
		$intentID = 1;
		$templates = array('testInsertNewIntentExpressionsForTemplates_Single <replace1>');
		$replace1 = new \stdClass();
		$replace1->key = '<replace1>';
		$replace1->wit_id = '1';
		$replace1->values = array((object) ['textValue'=>'a', 'witValue'=>'a']);
		$substitutions = array($replace1);
        $intentExpressionService = new IntentExpressionService();
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
		$intentExpression = TestingUtil::getSingleRow("select *
				from ".MYSQL_DBNAME.".srvc_intent_expressions
				where expression_text like 'testInsertNewIntentExpressionsForTemplates_Single%'", IntentExpression::class);
		$this->assertNotFalse($intentExpression);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_Single a', $intentExpression->expression_text);
		$this->assertEquals('[{"wisp":"1","start":50,"end":51,"value":"a"}]', $intentExpression->entity_json);
	}
	
	public function testInsertNewIntentExpressionsForTemplates_Multiple() {
		$intentID = 1;
		$templates = array('testInsertNewIntentExpressionsForTemplates_Multiple <replace1> and <replace2>');
		$replace1 = new \stdClass();
		$replace1->key = '<replace1>';
		$replace1->wit_id = '1';
		$replace1->values = array((object) ['textValue'=>'a', 'witValue'=>'a']);
		$replace2 = new \stdClass();
		$replace2->key = '<replace2>';
		$replace2->wit_id = '2';
		$replace2->values = array((object) ['textValue'=>'b', 'witValue'=>'b']);
		$substitutions = array($replace1, $replace2);
        $intentExpressionService = new IntentExpressionService();
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
		$intentExpression = TestingUtil::getSingleRow("select *
				from ".MYSQL_DBNAME.".srvc_intent_expressions
				where expression_text like 'testInsertNewIntentExpressionsForTemplates_Multiple%'", IntentExpression::class);
		$this->assertNotFalse($intentExpression);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_Multiple a and b', $intentExpression->expression_text);
		$this->assertEquals('[{"wisp":"1","start":52,"end":53,"value":"a"},{"wisp":"2","start":58,"end":59,"value":"b"}]', $intentExpression->entity_json);
	}
	
	public function testInsertNewIntentExpressionsForTemplates_DuplicateKey() {
		$intentID = 1;
		$templates = array('testInsertNewIntentExpressionsForTemplates_DuplicateKey');
		$substitutions = array();
        $intentExpressionService = new IntentExpressionService();
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
		$intentExpression = TestingUtil::getSingleRow("select * 
				from ".MYSQL_DBNAME.".srvc_intent_expressions 
				where expression_text = 'testInsertNewIntentExpressionsForTemplates_DuplicateKey'", IntentExpression::class);
		$this->assertNotFalse($intentExpression);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_DuplicateKey', $intentExpression->expression_text);
		$this->assertEquals('[]', $intentExpression->entity_json);
	}
	
	public function testInsertNewIntentExpressionsForTemplates_MultipleTemplates() {
		$intentID = 1;
		$templates = array(
			'testInsertNewIntentExpressionsForTemplates_MultipleTemplates1 <replace1> and <replace2>',
			'testInsertNewIntentExpressionsForTemplates_MultipleTemplates2 <replace1> and <replace2>');
		$replace1 = new \stdClass();
		$replace1->key = '<replace1>';
		$replace1->wit_id = '1';
		$replace1->values = array((object) ['textValue'=>'a', 'witValue'=>'a']);
		$replace2 = new \stdClass();
		$replace2->key = '<replace2>';
		$replace2->wit_id = '2';
		$replace2->values = array((object) ['textValue'=>'b', 'witValue'=>'b']);
		$substitutions = array($replace1, $replace2);
        $intentExpressionService = new IntentExpressionService();
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
		$intentExpression = TestingUtil::getSingleRow("select *
				from ".MYSQL_DBNAME.".srvc_intent_expressions
				where expression_text like 'testInsertNewIntentExpressionsForTemplates_MultipleTemplates1%'", IntentExpression::class);
		$intentExpression2 = TestingUtil::getSingleRow("select *
				from ".MYSQL_DBNAME.".srvc_intent_expressions
				where expression_text like 'testInsertNewIntentExpressionsForTemplates_MultipleTemplates2%'", IntentExpression::class);
		$this->assertNotFalse($intentExpression);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_MultipleTemplates1 a and b', $intentExpression->expression_text);
		$this->assertEquals('[{"wisp":"1","start":62,"end":63,"value":"a"},{"wisp":"2","start":68,"end":69,"value":"b"}]', $intentExpression->entity_json);
		$this->assertNotFalse($intentExpression2);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_MultipleTemplates2 a and b', $intentExpression2->expression_text);
		$this->assertEquals('[{"wisp":"1","start":62,"end":63,"value":"a"},{"wisp":"2","start":68,"end":69,"value":"b"}]', $intentExpression2->entity_json);
	}
	
	public function testInsertNewIntentExpressionsForTemplates_MissingSubstitutions() {
		$intentID = 1;
		$templates = array(
				'testInsertNewIntentExpressionsForTemplates_MissingSubstitutions <replace2>');
		$replace1 = new \stdClass();
		$replace1->key = '<replace1>';
		$replace1->wit_id = '1';
		$replace1->values = array((object) ['textValue'=>'a', 'witValue'=>'a']);
		$replace2 = new \stdClass();
		$replace2->key = '<replace2>';
		$replace2->wit_id = '2';
		$replace2->values = array((object) ['textValue'=>'b', 'witValue'=>'b']);
		$substitutions = array($replace1, $replace2);
        $intentExpressionService = new IntentExpressionService();
        $intentExpressionService->insertNewIntentExpressionsForTemplates($intentID, $templates, $substitutions);
		$intentExpression = TestingUtil::getSingleRow("select *
				from ".MYSQL_DBNAME.".srvc_intent_expressions
				where expression_text like 'testInsertNewIntentExpressionsForTemplates_MissingSubstitutions%'", IntentExpression::class);
		$this->assertNotFalse($intentExpression);
		$this->assertEquals('testInsertNewIntentExpressionsForTemplates_MissingSubstitutions b', $intentExpression->expression_text);
		$this->assertEquals('[{"wisp":"2","start":64,"end":65,"value":"b"}]', $intentExpression->entity_json);
	}
	
	public static function tearDownAfterClass() {
		static::removeTestData();
	}
	
	private static function removeTestData() {
		TestingUtil::executeSQL("delete from ".MYSQL_DBNAME.".srvc_intent_expressions 
				where expression_text like 'testInsertNewIntentExpressionsForTemplates%'");
	}
	
}