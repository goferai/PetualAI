<?php

namespace PetualAI\SDK\DevAI;

use PetualAI\Util\CypherQueryUtil;
use Ramsey\Uuid\Uuid;
use PetualAI\Util\DateUtil;

class Action {

	/**
	 * UUID4
	 * @var string
	 */
	public $id;
	public $class_name;

	/**
	 * The string representing of what to run. Pass in exactly what the call would be and it'll get run dynamically
	 * Example $object->$subProperty->method() like $email->buildAutoMeeting()->
	 * @var string
	 */
	public $function;

	/**
	 * ISO 8601 date string in utc timezone
	 * @var string
	 */
	public $create_date;

	/**
	 * Unix timestamp
	 * @var int
	 */
	public $create_timestamp;

	public function initializeNew() {
		$this->id = Uuid::uuid4()->toString();
		$this->create_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
		$this->create_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
	}

	public function initializeForData($data) {
		$this->initializePropertiesForObject($data);
	}


	public function initializeForID($id) {
		$this->setupConnection('neo4j');
		$cypherQueryUtil = new CypherQueryUtil($this->connection);
		$query = "match (a:Action) where a.id = '$id' return a";
		$results = $cypherQueryUtil->select($query, get_class($this));
		if (!$results || count($results) !== 1) return;
		$this->initializePropertiesForObject($results[0]);
	}

	/**
	 * Get actions - returns empty array if nothing is found
	 * @return Action[]
	 */
	public function getActions($search = null, $skip = null, $limit = null, $className = null) {
		$this->setupConnection('neo4j');
		$cypherQueryUtil = new CypherQueryUtil($this->connection);
		$whereFilters = [];
		if (isset($search) && strlen($search) >= 1) {
			array_push($whereFilters, "a.function =~ '(?i)$search.*' ");
		}
		$slash = '\\';
		if (isset($className) && strlen($className) >= 1) {
			array_push($whereFilters, "a.class_name = '".str_replace($slash, $slash.$slash, $className)."' ");
		}
		$where = (count($whereFilters) >= 1) ? 'where '.implode(' and ', $whereFilters) : '';
		$skipClause = '';
		if (isset($skip)) {
			$skipClause = 'skip '.$skip;
		}

		$limitClause = '';
		if (isset($limit)) {
			$limitClause = 'limit '.$limit;
		}

		$query = "match (a:Action) $where
				return a
				order by a.class_name, a.function
				$skipClause
				$limitClause";
		$results = $cypherQueryUtil->select($query, get_class($this));
		$this->log->debug('results = '.json_encode($results));
		if (!$results || count($results) === 0) return [];
		return $results;
	}

	public function insert() {
		$this->setupConnection('neo4j');
		$cypherQueryUtil = new CypherQueryUtil($this->connection);
		$query = "CREATE (:Action {id: '$this->id', class_name:{params}.class_name, function:{params}.function, create_date: '$this->create_date', create_timestamp: $this->create_timestamp});";
		$params = ['params' => [
				'class_name' => $this->class_name,
				'function' => $this->function,
				'id' => $this->id
		]];
		$cypherQueryUtil->execute($query, $params);
	}

	public function update() {
		$this->setupConnection('neo4j');
		$query = "match (a:Action) where a.id = {id} set a.class_name = {class_name}, a.function = {function};";
		$params = ['id' => $this->id, 'class_name' => $this->class_name, 'function' => $this->function];
		$this->log->debug('$query, $params = '.$query.' | '.json_encode($params));
		$this->connection->db->run($query, $params);
	}

}