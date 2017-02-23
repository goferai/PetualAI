<?php

namespace PetualAI\SDK\DevAI;

use PetualAI\SDK\IObject;
use Ramsey\Uuid\Uuid;
use PetualAI\Util\DateUtil;
use PetualAI\Util\CypherQueryUtil;

class Experiment extends IObject {

	public $id;
	public $intended_interaction_id;
	public $actual_interaction_id;


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

	/**
	 * @var int
	 */
	public $order = 0;

	/**
	 * @var Interaction $intendedInteraction
	 */
	public $intendedInteraction;

	/**
	 * @var Interaction $actualInteraction
	 */
	public $actualInteraction;

	/**
	 * Whether an admin reviewed this experiment and said it was good. Defaults to false
	 * @var boolean
	 */
	public $admin_confirmed;

	protected $transaction;

	public function setTransaction($transaction) {
		$this->transaction = $transaction;
	}

	public function initializeNew() {
		$this->id = Uuid::uuid4()->toString();
		$this->create_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
		$this->create_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
	}

	public function initializeForInteractions($intendedInteraction, $actualInteraction, $order = 0) {
		$this->id = Uuid::uuid4()->toString();
		$this->intendedInteraction = $intendedInteraction;
		$this->intended_interaction_id = $this->intendedInteraction->id;
		$this->actualInteraction = $actualInteraction;
		$this->actual_interaction_id = $this->actualInteraction->id;
		$this->create_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
		$this->create_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
		$this->order = $order;
		$this->admin_confirmed = false;
	}

	public function initializeForData($data) {
		$this->initializePropertiesForObject($data);
	}

	public function getExperiments($search = null, $skip = null, $limit = null) {
		$this->setupConnection('neo4j');
		$cypherQueryUtil = new CypherQueryUtil($this->connection);
		$searchFilter = (isset($search) && strlen($search) >= 1) ? "where a.function =~ '(?i)$search.*' " : '';

		$skipClause = '';
		if (isset($skip)) {
			$skipClause = 'skip '.$skip;
		}

		$limitClause = '';
		if (isset($limit)) {
			$limitClause = 'limit '.$limit;
		}

		$query = "match (intended:Interaction)<-[:INTENDED_INTERACTION]-(e:Experiment)-[:ACTUAL_INTERACTION]->(actual:Interaction) $searchFilter
// 				// Get the actions for the intended interaction
// 				optional match path_intended=(intended)-[r*0..100]->(base_intended)-[:RUNS_ACTION]->(action_intended:Action)
// 				with e, intended, actual, base_intended, action_intended,
// 					extract(
// 					r IN relationships(path_intended) |
// 					REDUCE(
// 					    c=-1,
// 					    indx in range(0, size(relationships(path_intended))-1) |
// 					    case when relationships(path_intended)[indx] = r then indx else c end
// 					) + case when type(r) = 'RUNS_PRE_INTERACTION' then '1' else '2' end
// 					) as rel_index_and_order
// 				with e, intended, actual, base_intended, action_intended, REDUCE(s='', r in rel_index_and_order | s+r ) as path_sort_intended
// 				order by e.create_timestamp desc, path_sort_intended
// 				with e, intended, actual, collect(action_intended) as actions_intended,  collect(base_intended) as base_interactions_intended

// 				//Repeat for the actual interactions
// 				optional match path_actual=(actual)-[r*0..100]->(base_actual)-[:RUNS_ACTION]->(action_actual:Action)
// 				with e, intended, actual, base_interactions_intended, actions_intended, base_actual, action_actual,
// 					extract(
// 					r IN relationships(path_actual) |
// 					REDUCE(
// 					    c=-1,
// 					    indx in range(0, size(relationships(path_actual))-1) |
// 					    case when relationships(path_actual)[indx] = r then indx else c end
// 					) + case when type(r) = 'RUNS_PRE_INTERACTION' then '1' else '2' end
// 					) as rel_index_and_order
// 				with e, intended, actual, base_interactions_intended, actions_intended, base_actual, action_actual, REDUCE(s='', r in rel_index_and_order | s+r ) as path_sort_actual
// 				order by e.create_timestamp desc, path_sort_actual
// 				return e, intended, actual, base_interactions_intended, actions_intended, collect(action_actual) as actions_actual,  collect(base_actual) as base_interactions_actual
				return e, intended, actual
				order by e.create_timestamp desc
				$skipClause
				$limitClause";
		$this->log->debug('query = '.$query);
		$result = $this->connection->db->run($query);
		$records = $result->getRecords();
		$experiments = array();
		foreach ($records as $record) {
			$experiment = $cypherQueryUtil->convertNodeToObject($record->get('e'), get_class($this));
			$intendedInteraction = $cypherQueryUtil->convertNodeToObject($record->get('intended'), Interaction::class);
			$intendedInteraction->initializeForID($intendedInteraction->id);
// 			$intendedInteraction->buildActionsForCypherNodes($record->get('base_interactions_intended'), $record->get('actions_intended'), $cypherQueryUtil);
// 			$intendedInteraction->calculateActionString();

			$actualInteraction = $cypherQueryUtil->convertNodeToObject($record->get('actual'),  Interaction::class);
			$actualInteraction->initializeForID($actualInteraction->id);
// 			$actualInteraction->buildActionsForCypherNodes($record->get('base_interactions_actual'), $record->get('actions_actual'), $cypherQueryUtil);
// 			$actualInteraction->calculateActionString();

			$experiment->intendedInteraction = $intendedInteraction;
			$experiment->intended_interaction_id = $intendedInteraction->id;
			$experiment->actualInteraction = $actualInteraction;
			$experiment->actual_interaction_id = $actualInteraction->id;
			array_push($experiments, $experiment);
		}
		return $experiments;
	}

	public function insert() {
		$this->log->debug('uspert'.json_encode($this));
		$query = "match (intended:Interaction) where intended.id ={params}.intended_interaction_id
			match (actual:Interaction) where actual.id ={params}.actual_interaction_id
			CREATE (intended)<-[r1:INTENDED_INTERACTION]-(e:Experiment {id : {params}.id, create_date : '$this->create_date', create_timestamp : $this->create_timestamp})-[r2:ACTUAL_INTERACTION]->(actual);";
		$params = ['params' => [
				'intended_interaction_id' => $this->intended_interaction_id,
				'actual_interaction_id' => $this->actual_interaction_id,
				'id' => $this->id
		]];
		$this->log->debug('query = '.$query.' --- '.json_encode($params));
		if (isset($this->transaction)) {
			$this->transaction->run($query, $params);

		} else {
			$this->setupConnection('neo4j');
			$this->connection->db->run($query, $params);
		}
	}

	public function updateActualInteration() {
		$this->log->debug('fixActualInteration'.json_encode($this));
		$query = "match (e:Experiment)-[rCurrent:ACTUAL_INTERACTION]->(actualCurrent) where e.id = '$this->id'
				delete rCurrent
				with e
				match (actualNew) where actualNew.id = '$this->actual_interaction_id'
				merge (e)-[rNew:ACTUAL_INTERACTION]->(actualNew);";
		$this->log->debug('query = '.$query);
		if (isset($this->transaction)) {
			$this->transaction->run($query);
		} else {
			$this->setupConnection('neo4j');
			$this->connection->db->run($query);
		}
	}

	public function delete() {
		$this->log->debug('delete'.json_encode($this));
		$query = "match ()-[r]-(e:Experiment) where e.id = '$this->id' delete r, e;";
		$this->log->debug('query = '.$query);
		if (isset($this->transaction)) {
			$this->transaction->run($query);

		} else {
			$this->setupConnection('neo4j');
			$this->connection->db->run($query);
		}
	}

}