<?php
namespace PetualAI\SDK\DevAI;

use PetualAI\SDK\IObject;
use PetualAI\Util\CypherQueryUtil;
use Ramsey\Uuid\Uuid;
use PetualAI\Util\DateUtil;
use PetualAI\SDK\Actions\EmailActions;
use PetualAI\SDK\Actions\MessageActions;
use PetualAI\Util\Neo4jConnectionManager;

class Interaction extends IObject {

    /**
     * @var string UUIDs
     */
    public $id;

    /**
     * Only filled in if this is a base action. Should also have a result.
     * @var string
     */
    public $action_id;

    /**
     * Only available for base actions
     * @var int
     */
    public $result;

    /**
     * @var int
     */
    public $reward;

    /**
     * @var string
     */
    public $pre_interaction_id;

    /**
     * @var string
     */
    public $post_interaction_id;

    /**
     * Whether we should attempt to propose running this interaction. Defaults to true. If false then this will only ever be the actual_interaction in an experiment - never the intended interaction.
     * @var boolean
     */
    public $proposable_flag;

    /**
     * Calculated when comparing what to pick
     * @var int
     */
    public $total_score;

    /**
     * Array of the actions that will be run for compound interactions
     * @var Action[]
     */
    public $actions_list;

    /**
     * Same as the actions_list but contains the base interaction details
     * @var Interaction[]
     */
    public $base_interactions_list;

    /**
     * If this is a base interaction linked to a single action
     * @var Action $action
     */
    public $action;

    /**
     * @var Interaction $preInteraction
     */
    public $preInteraction;

    /**
     * @var Interaction $postInteraction
     */
    public $postInteraction;

    /**
     * True or false on whether this is a base action or a compound interaction with a pre and a post
     * @var boolean
     */
    public $is_base_action;

    public $_level;
    public $_actionString;

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
        $this->proposable_flag = true;
    }

    public function initializeForData($data) {
        $this->initializePropertiesForObject($data);

        if (isset($this->actions_list)) {
            $this->actions_list = array();
            foreach ($data->actions_list as $action) {
                $newAction = new Action();
                $newAction->setConnection($this->connection);
                $newAction->initializeForData($action);
                array_push($this->actions_list, $newAction);
            }
        }

        if (isset($this->preInteraction)) {
            $preInteraction = new Interaction();
            $preInteraction->setConnection($this->connection);
            $preInteraction->initializeForData($data->preInteraction);
            $this->preInteraction = $preInteraction;
        }

        if (isset($this->postInteraction)) {
            $postInteraction = new Interaction();
            $postInteraction->setConnection($this->connection);
            $postInteraction->initializeForData($data->postInteraction);
            $this->postInteraction = $postInteraction;
        }

        if (isset($this->action)) {
            $this->action = new Action();
            $this->action->setConnection($this->connection);
            $this->action->initializeForData($data->action);
        }
    }

    public function initializeForStartForActionClass($className) {
        $slash = '\\';
        switch ($className) {
            case $slash.EmailActions::class:
                $this->initializeLimitedForID('921a222c-257c-449f-a099-02d5475da1ba');
                break;
            case $slash.MessageActions::class:
                $this->initializeLimitedForID('7eac4e20-a031-4418-9326-5b54dacd13df');
                break;
        }
    }

    public function initializeForID($id) {
        $interactions = $this->getInteractions(['ids'=>$id]);
        if (count($interactions) === 0) return;
        $this->initializeForData($interactions[0]);
    }

    public function initializeLimitedForID($id) {
        $this->log->debug('initializeLimitedForID id = '.$id);
        $this->setupConnection('neo4j');
        $cypherQueryUtil = new CypherQueryUtil($this->connection);
        $query = "match (i:Interaction) where i.id = {params}.id
				optional match (pre)<-[:RUNS_PRE_INTERACTION]-(i)-[:RUNS_POST_INTERACTION]->(post)
				optional match (i)-[:RUNS_ACTION]->(action:Action)
				return i, action, pre, post";
        $params = ['params' => [
            'id' => $id,
        ]];
        $result = $this->connection->db->run($query, $params);
        $records = $result->getRecords();
        if (count($records) !== 1) return;
        $record = $records[0];
        $interaction = $cypherQueryUtil->convertNodeToObject($record->get('i'), get_class($this));
        if ($interaction->is_base_action === true) {
            $interaction->action = $cypherQueryUtil->convertNodeToObject($record->get('action'), Action::class);
            $interaction->action_id = $interaction->action->id;
        } else {
            $interaction->preInteraction = $cypherQueryUtil->convertNodeToObject($record->get('pre'), get_class($this));
            $interaction->pre_interaction_id = $interaction->preInteraction->id;
            $interaction->postInteraction = $cypherQueryUtil->convertNodeToObject($record->get('post'), get_class($this));
            $interaction->post_interaction_id = $interaction->postInteraction->id;
        }
        $this->initializeForData($interaction);
        $this->log->debug('initializeLimitedForID final interaction = '.$this->toJSON());
    }

    /**
     * @param Interaction $preInteraction
     * @param Interaction $postInteraction
     */
    public function initializeForPreAndPost($preInteraction, $postInteraction) {
        $this->id = Uuid::uuid4()->toString();
        //$this->reward = $preInteraction->reward + $postInteraction->reward;
        $this->preInteraction = $preInteraction;
        $this->pre_interaction_id = $this->preInteraction->id;
        $this->postInteraction = $postInteraction;
        $this->post_interaction_id = $this->postInteraction->id;
        $this->create_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
        $this->create_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
    }

    /**
     * Will initialize it if already exists - otherwise it will add it
     * @param Interaction $preInteraction
     * @param Interaction $postInteraction
     */
    public function tryInitializeForPreAndPost($preInteraction, $postInteraction) {
        $interaction = $this->getLimitedInteractionForPrePost($preInteraction, $postInteraction);
        if (!$interaction) {
            $this->initializeForPreAndPost($preInteraction, $postInteraction);
            $this->upsert();
            return;
        }
        $this->initializePropertiesForObject($interaction);
    }

    public function tryInitializeForActionAndResult($action, $result, $reward = 0) {
        $interaction = $this->getLimitedInteractionForActionResult($action, $result);
        if (!$interaction) {
            $this->initializeForActionAndResult($action, $result, $reward);
            $this->upsert();
            return;
        }
        $this->initializePropertiesForObject($interaction);
    }

    /**
     * @param Action $action
     * @param int $result
     */
    public function initializeForActionAndResult($action, $result, $reward = 0) {
        $this->id = Uuid::uuid4()->toString();
        $this->reward = $reward;
        $this->action = $action;
        $this->action_id = $action->id;
        $this->result = $result;
        $this->create_date = DateUtil::getCurrentDateTimeUTC()->format(DateUtil::FORMAT_ISO8601);
        $this->create_timestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
        $this->is_base_action = true;
    }

    /**
     * Initialize the interaction to run next
     * @param string[] $currentPreInteractionIDs
     */
    public function initializeInteractionToRun($currentPreInteractionIDs = array('0'), $className = null) {
        $this->log->debug('initializeInteractionToRun - $currentPreInteractionIDs = '. json_encode($currentPreInteractionIDs));
        $this->setupConnection('neo4j');

        /*
         * Settings
         */
        $counterThreshold = 1; //Set this lower to explore less before settling - set higher to explore more but learn slower
        $maxDaysAgoThreshold = 365; //Only consider past experiments in last X days (older ones are still discounted)
        $epsilon = 0; // The % of time to explore a random interaction. 1 =  always random. 0 = never pick a random one
        $explorationBonus = 100; // Bonus score to award to interactions we have never tried... so we try things we don't know anything about yet. Decreases exponentially as we try it a few times.

        $currentTimestamp = DateUtil::getCurrentDateTimeUTC()->getTimestamp();
        $maxDaysAgoThresholdDate = DateUtil::getCurrentDateTimeUTC();
        $maxDaysAgoThresholdDate->modify("-$maxDaysAgoThreshold days");
        $maxDaysAgoThresholdTimestamp = $maxDaysAgoThresholdDate->getTimestamp();
        $classFilter = (isset($className)) ? " and a.class_name = {params}.className" : '' ;
        $params = ['params' => [
            'className' => $className,
            'currentPreInteractionIDs' => $currentPreInteractionIDs,
            'counterThreshold' => $counterThreshold,
            'epsilon' => $epsilon,
            'maxDaysAgoThresholdTimestamp' => $maxDaysAgoThresholdTimestamp,
            'maxDaysAgoThreshold' => $maxDaysAgoThreshold,
            'currentTimestamp' => $currentTimestamp,
            'explorationBonus' => $explorationBonus
        ]];
        $query = "
			// Get all interactions and calculate the total rewards for the whole path
			match p=(i:Interaction)-[*1..100]->(a:Action)
				where case when i.proposable_flag is null then true else i.proposable_flag end = true $classFilter
			with p, i, reduce(t=0, x in nodes(p) | t + case when x.reward is not null then x.reward else 0 end) as single_path_reward
			with i, sum(single_path_reward) as total_reward
			// Outer join to get ones that match a preInteraction that just ran previously
			optional match (preInteraction)<-[:RUNS_PRE_INTERACTION]-(compoundInteraction)-[:RUNS_POST_INTERACTION]->(i)
				where preInteraction.id in {params}.currentPreInteractionIDs // and compoundInteraction.counter >= {params}.counterThreshold
			// Filter for only ones we care to consider now !!!!!!!!! CHANGE EPSILON TO LIMIT EXPLORATION
			with
				i, total_reward, compoundInteraction,
				case when compoundInteraction is not null then 1 else 0 end as proposed_flag,
				case when rand() <= {params}.epsilon then 1 else 0 end as random_chosen_flag
			where i.is_base_action = true or proposed_flag = 1 or random_chosen_flag = 1
			// Lookup all the times we previously tried this interaction Calcualte total counts and rewards for success and failed experiments
			optional match (compoundInteraction)<-[:INTENDED_INTERACTION]-(experiment:Experiment)-[:ACTUAL_INTERACTION]->(actual)
				where experiment.create_timestamp > {params}.maxDaysAgoThresholdTimestamp
			optional match p2=(actual)-[*1..100]->(:Action)
			with
				i, total_reward, compoundInteraction, proposed_flag, random_chosen_flag,
				actual,
				experiment,
				reduce(t=0, x in nodes(p2) | t + case when x.reward is not null then x.reward else 0 end) as actual_total_reward
			with
				i, total_reward, compoundInteraction, proposed_flag, random_chosen_flag,
				sum(case when compoundInteraction.id = actual.id
					then ( (toFloat({params}.maxDaysAgoThreshold ) - (toFloat(abs({params}.currentTimestamp - experiment.create_timestamp))/toFloat(60)/toFloat(60)/toFloat(24)) ) / toFloat({params}.maxDaysAgoThreshold)) end) as success_count,
				sum(case when compoundInteraction.id <> actual.id
					then ( (toFloat({params}.maxDaysAgoThreshold ) - (toFloat(abs({params}.currentTimestamp - experiment.create_timestamp))/toFloat(60)/toFloat(60)/toFloat(24)) ) / toFloat({params}.maxDaysAgoThreshold))  end) as failed_count,
				sum(case when compoundInteraction.id <> actual.id then actual_total_reward end) as failed_reward,
				count(actual.id) as attempted_count_anytime
			where case when compoundInteraction is not null then attempted_count_anytime else 999 end >= {params}.counterThreshold
			// Filter for the best one
			with
				i, total_reward, compoundInteraction, proposed_flag, random_chosen_flag,
				(total_reward*success_count) as success_score,
				failed_reward * failed_count as failure_multiplier,
				{params}.explorationBonus * (toFloat(3)/toFloat(exp(attempted_count_anytime)+1)) as exploration_bonus //exp is there to make it decrease the bonus very quickly once we've tried that interaction
			with
				i, total_reward, compoundInteraction, proposed_flag, random_chosen_flag, success_score, failure_multiplier, exploration_bonus,
				success_score + failure_multiplier + exploration_bonus as final_score,
				rand() as random_tiebreaker
			order by
				final_score desc,
				random_tiebreaker
			limit 1
			return i.id as id
			//return i, total_reward, compoundInteraction, proposed_flag, random_chosen_flag, success_score, failure_multiplier, exploration_bonus, final_score, random_tiebreaker
			";
        $this->log->debug('initializeInteractionToRun - query = '.Neo4jConnectionManager::populateParamsInQueryString($query, $params));
        $result = $this->connection->db->run($query, $params);
        $records = $result->getRecords();
        if (!$records || count($records) !== 1) return false;
        $id = $records[0]->get('id');
        $this->log->debug('initializeInteractionToRun - $id = '. $id);
        $this->initializeLimitedForID($id);
    }

    /**
     * Get interactions
     * @param array $params Pass the optional params you need as key=> value pairs. Options are:
     * @param string|string[] $id Pass a single string or an array of strings
     * @param Interaction $preInteraction
     * @param Interaction $postInteraction
     * @param Action $action
     * @param int $result
     * @param string $search
     * @param skip $result
     * @param limit $result
     */
    public function getInteractions($params = []) {
        $this->log->debug('getInteractions start');
        $this->setupConnection('neo4j');
        $cypherQueryUtil = new CypherQueryUtil($this->connection);

        $preMatchClause = 'match (i:Interaction) with i';
        if (isset($params['ids'])) {
            $ids = (array) $params['ids'];
            $preMatchClause = "match (i:Interaction) where i.id in ['".implode("','",$ids)."'] with i";
        }

        if (isset($params['preInteraction']) && isset($params['postInteraction'])) {
            $preMatchClause = "match (preFilter {id:'".$params['preInteraction']->id."'})<-[:RUNS_PRE_INTERACTION]-(i:Interaction)-[:RUNS_POST_INTERACTION]->(postFilter {id:'".$params['postInteraction']->id."'})
								with i";
        }

        if (isset($params['action']) && isset($params['result'])) {
            $this->log->debug('action = '.json_encode($params['action']));
            $preMatchClause = "match (i:Interaction {result: ".$params['result']." })-[:RUNS_ACTION]->(action:Action {id:'".$params['action']->id."'})
								with i";
        }

        $slash = '\\';
        $actionFilter = '';
        if (isset($params['className'])) {
            $actionFilter = " and a.class_name = '".str_replace($slash, $slash.$slash, $params['className'])."'";
        }

        if (isset($params['search'])) {
            $preMatchClause = "match (i:Interaction)-[r*1..100]->(a:Action)
								where a.function =~ '(?i)".$params['search'].".*' $actionFilter
								with i";
        } elseif (isset($params['className'])) {
            $preMatchClause = "match (i:Interaction)-[r*1..100]->(a:Action)
								where a.class_name = '".str_replace($slash, $slash.$slash, $params['className'])."'
								with i";
        }

        $skipClause = '';
        if (isset($params['skip'])) {
            $skipClause = 'skip '.$params['skip'];
        }

        $limitClause = '';
        if (isset($params['limit'])) {
            $limitClause = 'limit '.$params['limit'];
        }

        $query = "
			$preMatchClause

			//Get base interactions
			optional match (i)-[:RUNS_ACTION]->(base_action:Action)
			with i, base_action

			//Get pre interaction
			optional match p=(i:Interaction)-[:RUNS_PRE_INTERACTION]->(pre)-[r*0..100]->(base)-[:RUNS_ACTION]->(action:Action)
			with
				i, base_action,
				pre,
			    	p,
				action,
				case when base is null then pre else base end as base,
			    extract(
				r IN relationships(p) |
				REDUCE(
				    c=-1,
				    indx in range(0, size(relationships(p))-1) |
				    case when relationships(p)[indx] = r then indx else c end
				) + case when type(r) = 'RUNS_PRE_INTERACTION' then '1' else '2' end
			    ) as rel_index_and_order,
			    reduce(t=0, x in nodes(p) | t + case when x.reward is not null then x.reward else 0 end) as path_reward
			with
				i, base_action,
				pre,
				p,
				action,
				base,
				REDUCE(s='', r in rel_index_and_order | s+r ) as path_sort,
				path_reward
			order by path_sort
			with i, base_action, pre, collect(base) as pre_base_interactions, collect(action) as pre_actions, sum(path_reward) as pre_reward, count(action) as pre_action_count

			//Get post interaction
			optional match p2=(i)-[:RUNS_POST_INTERACTION]->(post)-[r2*0..100]->(base2)-[:RUNS_ACTION]->(action2:Action)
			with
				i, base_action,
				pre, pre_base_interactions, pre_actions, pre_reward, pre_action_count,
				post,
			    	p2,
				action2,
				case when base2 is null then post else base2 end as base2,
			    extract(
				r IN relationships(p2) |
				REDUCE(
				    c=-1,
				    indx in range(0, size(relationships(p2))-1) |
				    case when relationships(p2)[indx] = r then indx else c end
				) + case when type(r) = 'RUNS_PRE_INTERACTION' then '1' else '2' end
			    ) as rel_index_and_order2,
			    reduce(t=0, x in nodes(p2) | t + case when x.reward is not null then x.reward else 0 end) as path_reward2
			with
				i, base_action,
				pre, pre_base_interactions, pre_actions, pre_reward, pre_action_count,
				post,
				p2,
				action2,
				base2,
				REDUCE(s='', r in rel_index_and_order2 | s+r ) as path_sort2,
				path_reward2
			order by path_sort2
			with i, base_action,
			pre, pre_base_interactions, pre_actions, pre_reward, pre_action_count,
			post, collect(base2) as post_base_interactions, collect(action2) as post_actions, sum(path_reward2) as post_reward, count(action2) as post_action_count

			return
				i,
				base_action as action,
				base_action.id as action_id,
				case when base_action is null then pre_reward + post_reward else i.reward end as reward,
				pre, post, pre_base_interactions, pre_actions, pre_reward, pre_action_count,
				post_base_interactions, post_actions, post_reward, post_action_count,
				case when i.proposable_flag is null then true else i.proposable_flag end as proposable_flag
			order by i.create_timestamp desc
			$skipClause
			$limitClause";
        $this->log->debug('getInteractions query = '.$query);
        $result = $this->connection->db->run($query);
        $records = $result->getRecords();
        $interactions = array();
        foreach ($records as $record) {
            $interactionNode = $record->get('i');
            $interaction = $cypherQueryUtil->convertNodeToObject($interactionNode, get_class($this));
            $interaction->proposable_flag = $record->get('proposable_flag');
            if ($record->hasValue('pre') && $record->get('pre') !== null) {
                $interaction->preInteraction = $cypherQueryUtil->convertNodeToObject($record->get('pre'), get_class($this));
                $interaction->pre_interaction_id = $interaction->preInteraction->id;
                $interaction->preInteraction->buildActionsForCypherNodes($record->get('pre_base_interactions'), $record->get('pre_actions'), $cypherQueryUtil);
            }
            if ($record->hasValue('post') && $record->get('post') !== null) {
                $interaction->postInteraction = $cypherQueryUtil->convertNodeToObject($record->get('post'), get_class($this));
                $interaction->post_interaction_id = $interaction->postInteraction->id;
                $interaction->postInteraction->buildActionsForCypherNodes($record->get('post_base_interactions'), $record->get('post_actions'), $cypherQueryUtil);
            }

            if ($interaction->isBaseAction()) {
                $interaction->action = $cypherQueryUtil->convertNodeToObject($record->get('action'), Action::class);
                $interaction->action_id = $interaction->action->id;
            }

            $interaction->reward = $record->get('reward');
            $interaction->calculateActionString();
            array_push($interactions, $interaction);
        }
        return $interactions;
    }

    public function buildActionsForCypherNodes($baseInteractions, $actions, $cypherQueryUtil) {
        if ($this->isBaseAction()) {
            $this->base_interactions_list = array($cypherQueryUtil->convertNodeToObject($baseInteractions[0], Interaction::class));
            $this->action = $cypherQueryUtil->convertNodeToObject($actions[0], Action::class);
            $this->actions_list = array($this->action);
            $this->action_id = $this->action->id;
        } else {
            $actionObjects = array();
            $baseInteractionObjects = array();
            $index = 0;
            foreach ($actions as $action) {
                array_push($actionObjects, $cypherQueryUtil->convertNodeToObject($action, Action::class));
                array_push($baseInteractionObjects, $cypherQueryUtil->convertNodeToObject($baseInteractions[$index], Interaction::class));
                $index++;
            }
            $this->actions_list = $actionObjects;
            $this->base_interactions_list = $baseInteractionObjects;
        }
    }

    public function calculateActionString() {
        if (!isset($this->action_id) && !isset($this->preInteraction)) return;
        if (isset($this->action_id)) {
            $this->_actionString = $this->action->function.' | '.strval($this->result);
        } else {
            if (!isset($this->preInteraction->base_interactions_list) || count($this->preInteraction->base_interactions_list) === 0) return;
            $this->_actionString = '';
            $index = 0;
            $pre = '';
            foreach($this->preInteraction->actions_list as $action) {
                $pre .=  ' > '.$action->function.' | '.$this->preInteraction->base_interactions_list[$index]->result;
                $index++;
            }
            $pre = substr($pre, 3);
            $index = 0;
            $post = '';
            foreach($this->postInteraction->actions_list as $action) {
                $post .=  ' > '.$action->function.' | '.$this->postInteraction->base_interactions_list[$index]->result;
                $index++;
            }
            $post = substr($post, 3);
            $this->_actionString = $pre. ' -->> '.$post;
        }
        $this->_actionString .= ' -->> ('.strval($this->reward).')';
    }

    /**
     * Simpler query that runs faster to just get the interaction without the extra stuff like the pre and post objects or the action list.
     * @param Action $action
     * @param int $result
     * @return boolean|stdClass|mixed
     */
    public function getLimitedInteractionForActionResult($action, $result) {
        $this->setupConnection('neo4j');
        $cypherQueryUtil = new CypherQueryUtil($this->connection);
        $query = "match (i:Interaction {result: {params}.result })-[:RUNS_ACTION]->(action:Action {id:{params}.action_id})
				return i, action";
        $params = ['params' => [
            'result' => $result,
            'action_id' => $action->id
        ]];
        $result = $this->connection->db->run($query, $params);
        $records = $result->getRecords();
        if (count($records) !== 1) return false;
        $record = $records[0];
        $interaction = $cypherQueryUtil->convertNodeToObject($record->get('i'), get_class($this));
        $interaction->action = $cypherQueryUtil->convertNodeToObject($record->get('action'), Action::class);
        $interaction->action_id = $interaction->action->id;
        return $interaction;
    }

    /**
     * Simpler query that runs faster to just get the interaction without the extra stuff like the full pre and post objects or the action list.
     * @param Interaction $preInteraction
     * @param Interaction $postInteraction
     * @return $this|false
     */
    public function getLimitedInteractionForPrePost($preInteraction, $postInteraction) {
        $this->setupConnection('neo4j');
        $cypherQueryUtil = new CypherQueryUtil($this->connection);
        $query = "match (pre)<-[:RUNS_PRE_INTERACTION]-(i:Interaction)-[:RUNS_POST_INTERACTION]->(post)
				where pre.id = {params}.pre_interaction_id and post.id = {params}.post_interaction_id
				return i, pre, post";
        $params = ['params' => [
            'pre_interaction_id' => $preInteraction->id,
            'post_interaction_id' => $postInteraction->id
        ]];
        $result = $this->connection->db->run($query, $params);
        $records = $result->getRecords();
        if (count($records) !== 1) return false;
        $record = $records[0];
        $interaction = $cypherQueryUtil->convertNodeToObject($record->get('i'), get_class($this));
        $interaction->preInteraction = $cypherQueryUtil->convertNodeToObject($record->get('pre'), get_class($this));
        $interaction->pre_interaction_id = $interaction->preInteraction->id;
        $interaction->postInteraction = $cypherQueryUtil->convertNodeToObject($record->get('post'), get_class($this));
        $interaction->post_interaction_id = $interaction->postInteraction->id;
        return $interaction;
    }

    public function upsert() {
        $this->setupConnection('neo4j');
        $this->log->debug('uspert'.json_encode($this));
        if (isset($this->action_id)) {
            $query = "match (a:Action) where a.id = {params}.action_id
					merge (i:Interaction {result:{params}.result, is_base_action:true })-[r:RUNS_ACTION]->(a)
					ON MATCH SET i.reward = {params}.reward
					ON CREATE SET i.id = {params}.id, i.reward = {params}.reward, i.create_date = '$this->create_date', i.create_timestamp = $this->create_timestamp ;";
            $params = ['params' => [
                'action_id' => $this->action_id,
                'result' => $this->result,
                'reward' => $this->reward,
                'id' => $this->id
            ]];
            $this->log->debug('query1 = '.Neo4jConnectionManager::populateParamsInQueryString($query, $params));
            $this->connection->db->run($query, $params);
        } elseif (isset($this->pre_interaction_id) && isset($this->post_interaction_id)) {
            $query = "match (pre:Interaction) where pre.id ={params}.pre_interaction_id
					match (post:Interaction) where post.id ={params}.post_interaction_id
					merge (pre)<-[r:RUNS_PRE_INTERACTION]-(i:Interaction {is_base_action:false})-[r2:RUNS_POST_INTERACTION]->(post)
					ON CREATE SET i.id = {params}.id, i.create_date = '$this->create_date', i.create_timestamp = $this->create_timestamp ;";
            $params = ['params' => [
                'pre_interaction_id' => $this->pre_interaction_id,
                'post_interaction_id' => $this->post_interaction_id,
                'id' => $this->id
            ]];
            $this->log->debug('query2 = '.Neo4jConnectionManager::populateParamsInQueryString($query, $params));
            $this->connection->db->run($query, $params);
        }
    }

    /**
     * @return boolean
     */
    public function hasID() {
        return isset($this->id);
    }

    /**
     * @return boolean
     */
    public function hasPreInteractionID() {
        return isset($this->pre_interaction_id);
    }

    /**
     * @return boolean
     */
    public function hasPreInteraction() {
        return isset($this->preInteraction);
    }

    /**
     * @return boolean
     */
    public function hasPostInteractionID() {
        return isset($this->post_interaction_id);
    }

    /**
     * @return boolean
     */
    public function hasPostInteraction() {
        return isset($this->postInteraction);
    }

    /**
     * Returns true if this is a base action that runs a real action instead of a higher level compound interaction comprising sub interactions/actions. Returns false if unknown
     * @return boolean
     */
    public function isBaseAction() {
        if (isset($this->action_id)) return true;
        if (isset($this->is_base_action)) return $this->is_base_action;
        return false;
    }

    public function action() {
        if (isset($this->action) && isset($this->action->function)) {
            return $this->action;
        }
        $this->initializeLimitedForID($this->id);
        return $this->action;
    }

    /**
     * Returns the preInteraction object (initializing it if it was not already done)
     */
    public function preInteraction() {
        if (isset($this->preInteraction)) {
            if (isset($this->preInteraction->action) || (isset($this->preInteraction->preInteraction))) {
                return $this->preInteraction;
            }
        }
        if (isset($this->pre_interaction_id)) {
            $this->preInteraction = new Interaction();
            $this->preInteraction->setConnection($this->connection);
            $this->preInteraction->initializeLimitedForID($this->pre_interaction_id);
            return $this->preInteraction;
        }
        $this->initializeLimitedForID($this->id);
        return $this->preInteraction;
    }

    /**
     * Returns the postInteraction object (initializing it if it was not already done)
     */
    public function postInteraction() {
        return $this->postInteraction;
    }

}