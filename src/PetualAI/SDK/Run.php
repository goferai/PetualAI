<?php

namespace PetualAI\SDK\DevAI;

use PetualAI\SDK\IObject;
use Ramsey\Uuid\Uuid;
use PetualAI\Util\CypherQueryUtil;
use PetualAI\Util\DateUtil;

/**
 * Represents the history of a run of exeriments in a sequence of thoughts
 */
class Run extends IObject {

	/**
	 * UUID4
	 * @var string
	 */
	public $id;

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

	public $completed_date;

	public $completed_timestamp;

	/**
	 * @var Experiment[]
	 */
	public $experiments = array();

	public function initializeNew() {
		$this->id = Uuid::uuid4()->toString();
		$this->create_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
		$this->create_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
	}

	public function initializeForData($data) {
		$this->initializePropertiesForObject($data);
	}

	/**
	 *
	 * @param string $id
	 * @param boolean $initializeInteractions Optional. If true then it will initialize the intended and actual interactions under the experiment. If false - then we will only know the ids. Default true.
	 */
	public function initializeForID($id, $initializeInteractions = true) {
		$this->setupConnection('neo4j');
		$cypherQueryUtil = new CypherQueryUtil($this->connection);
		$query = "
			match (run:Run)-->(e:Experiment)
				where run.id = '$id'
			with run, e
				order by e.order
			optional match (e)-[:INTENDED_INTERACTION]->(intended)
			optional match (e)-[:ACTUAL_INTERACTION]->(actual)
			return run, collect(e) as experiments, collect(intended) as intended, collect(actual) as actual;";
		$this->log->debug('query = '. $query);
		$result = $this->connection->db->run($query);
		$records = $result->getRecords();
		if (!$records || count($records) !== 1) return;
		$record = $result->getRecord();
		$run = $cypherQueryUtil->convertNodeToObject($record->get('run'), get_class($this));
		$this->initializePropertiesForObject($run);
		$experiments = $record->get('experiments');
		$intendedInteractions = $record->get('intended');
		$actualInteractions = $record->get('actual');
		$this->experiments = array();
		$index = 0;
		foreach ($experiments as $experiment) {
			$newExperiment = $cypherQueryUtil->convertNodeToObject($experiment, Experiment::class);
			$newExperiment->intended_interaction_id = $intendedInteractions[$index]->get('id');
			$newExperiment->actual_interaction_id = $actualInteractions[$index]->get('id');

			if ($initializeInteractions) {
				$intendedInteraction = new Interaction();
				$intendedInteraction->initializeForID($intendedInteractions[$index]->get('id'));
				$newExperiment->intendedInteraction = $intendedInteraction;


				$actualInteraction = new Interaction();
				$actualInteraction->initializeForID($actualInteractions[$index]->get('id'));
				$newExperiment->actualInteraction = $actualInteraction;

			}

			array_push($this->experiments, $newExperiment);
			$index++;
		}
	}

	public function fixExperiment($experimentID, $newExperiment) {
		$this->setupConnection('neo4j');
		$transaction = $this->connection->db->transaction();
		try {
			$experimentMatched = false;
			foreach($this->experiments as $experiment) {
				if ($experimentMatched) {
					$experiment->setTransaction($transaction);
					$experiment->delete();
				} else if ($experiment->id === $experimentID) {
					$experimentMatched = true;
					$experiment->setTransaction($transaction);
					$experiment->actual_interaction_id = $newExperiment->actual_interaction_id;
					$experiment->updateActualInteration();
				}
			}
			$transaction->commit();
		} catch (Exception $e) {
			$this->log->error('fixExperiment Error = ', $e);
			$transaction->rollback();
		}
	}

	public function insert() {
		$this->setupConnection('neo4j');
		$query = "
				CREATE (run:Run {id: '$this->id', create_date: '$this->create_date', create_timestamp: $this->create_timestamp});";
		$this->log->debug('insert - query = '.$query);
		$this->connection->db->run($query);
	}

	/**
	 * Marks the run completed - setting the completed date
	 */
	public function markCompleted() {
		$this->completed_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
		$this->completed_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
		$this->setupConnection('neo4j');
		$query = "
		merge (run:Run {id : '$this->id' })
		ON MATCH SET run.completed_date = '$this->completed_date', run.completed_date = $this->completed_timestamp";
		$this->log->debug('markCompleted query = '.$query);
		$this->connection->db->run($query);
	}

	/**
	 * @param Experiment $experiment
	 */
	public function addExperiment($experiment) {
		$this->addAndLinkExperimentToRun($experiment);
		array_push($this->experiments, $experiment);
	}

	public function getRuns() {
		$this->setupConnection('neo4j');
		$cypherQueryUtil = new CypherQueryUtil($this->connection);
		$query = "
			match (run:Run)-[:RUNS_EXPERIMENT]->(e)
			with run, e
			order by run.create_timestamp desc, e.order
			return run, collect(e) as experiments
			order by run.create_timestamp desc
			limit 100";
		$this->log->debug('query = '. $query);
		$result = $this->connection->db->run($query);
		$records = $result->getRecords();
		$runs = array();
		foreach ($records as $record) {
			$run = $cypherQueryUtil->convertNodeToObject($record->get('run'), get_class($this));
			$experiments = $record->get('experiments');
			$run->experiments = [];
			foreach( $experiments as $experiment) {
				array_push($run->experiments, $cypherQueryUtil->convertNodeToObject($experiment, Experiment::class));
			}
			array_push($runs, $run);
		}
		return $runs;
	}

	public function linkRunToObject($objectKey, $objectID) {
		$connection = new \PetualAI\Util\ConnectionManager();
		$sql = "insert into gofer.srvc_brain_runs (id, object_key, object_id) values (:id, :object_key, :object_id);";
		$this->log->debug('linkRunToObject - sql = '.$sql.' - $objectKey = '.$objectKey.' - '.$objectID);
		$stmt = $connection->db->prepare($sql);
		$stmt->bindParam(':id', $this->id);
		$stmt->bindParam(':object_key', $objectKey);
		$stmt->bindParam(':object_id', $objectID);
		$stmt->execute();
	}

	/**
	 * @param Experiment $experiment
	 */
	protected function addAndLinkExperimentToRun($experiment) {
		$this->setupConnection('neo4j');
		$query = "
				match (intended:Interaction) where intended.id = '$experiment->intended_interaction_id'
				match (actual:Interaction) where actual.id = '$experiment->actual_interaction_id'
				match (run:Run) where run.id = '$this->id'
				merge (e:Experiment {id : '$experiment->id', create_date: '$experiment->create_date', create_timestamp: $experiment->create_timestamp, order: $experiment->order })
				merge (intended)<-[:INTENDED_INTERACTION]-(e)
				merge (e)-[:ACTUAL_INTERACTION]->(actual)
				merge (run)-[:RUNS_EXPERIMENT]->(e);";
		$this->log->debug('addAndLinkExperimentToRun query = '.$query);
		$this->connection->db->run($query);
	}

}