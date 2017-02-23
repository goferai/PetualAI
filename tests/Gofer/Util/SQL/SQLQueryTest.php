<?php

use Gofer\SDK\Services\User;
use Gofer\Util\SQL\SQLConnection;
use Gofer\Util\SQL\SQLParameter;
use Gofer\Util\SQL\SQLParameterList;
use Gofer\Util\SQL\SQLQuery;
use Gofer\Util\TestingUtil;

class SQLQueryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider selectProvider
     * @param $sql
     * @param $class
     * @param $expectedCount
     */
	public function test_select($sql, $class, $expectedCount) {
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($sql);
        if (!empty($class)) {
            $sqlQuery->setClass($class);
        }
        $sqlQuery->select();
        $results = $sqlQuery->getResults();
        $this->assertEquals($expectedCount, $results->count());
        if (!empty($class)) {
            $this->assertInstanceOf($class, $results->first());
        }
	}

    public function selectProvider() {
	    $sqlParameterList1 = new SQLParameterList();
	    $sqlParameterList1->add(new SQLParameter(':id', TestingUtil::$mainTestUserID));
        return [
            ['select user_id as userId, user_name as userName, email from gofer.srvc_users where user_id = '.TestingUtil::$mainTestUserID, User::class, 1],
            ['select user_id as userId, user_name as userName, email from gofer.srvc_users where user_id = '.TestingUtil::$mainTestUserID, null, 1],
        ];
    }

    /**
     * @dataProvider selectSecureProvider
     * @param $sql
     * @param $class
     * @param SQLParameterList $sqlParameterList
     * @param $expectedCount
     */
    public function test_selectSecure($sql, $class, $sqlParameterList, $expectedCount) {
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($sql);
        if (!empty($class)) {
            $sqlQuery->setClass($class);
        }
        $sqlQuery->selectSecure($sqlParameterList);
        $results = $sqlQuery->getResults();
        $this->assertEquals($expectedCount, $results->count());
        if (!empty($class)) {
            $this->assertInstanceOf($class, $results->first());
        }
    }

    public function selectSecureProvider() {
        $sqlParameterList1 = new SQLParameterList();
        $sqlParameterList1->add(new SQLParameter(':id', TestingUtil::$mainTestUserID));
        return [
            ['select user_id as userId, user_name as userName, email from gofer.srvc_users where user_id = :id', User::class, $sqlParameterList1, 1],
            ['select user_id as userId, user_name as userName, email from gofer.srvc_users where user_id = :id', null, $sqlParameterList1, 1],
        ];
    }

    /**
     * Ensure it works with an in list
     */
    public function test_selectSecure_InList() {
        $ids = [TestingUtil::$mainTestUserID, TestingUtil::$mainTestAdminUserID];
        $conditionString = '';
        $sqlParameterList = new SQLParameterList();
        $sqlParameterList->buildMultipleForArray(':user_id', $ids, $conditionString, PDO::PARAM_INT);
        $sql = "select user_id as userId, user_name as userName, email from gofer.srvc_users where user_id in ($conditionString)";
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $results = $sqlQuery->setQuery($sql)
                            ->setClass(User::class)
                            ->selectSecure($sqlParameterList)
                            ->getResults();
        $this->assertEquals(2, $results->count());
        $this->assertInstanceOf(User::class, $results->first());
    }


}