<?php

namespace PetualAI\SDK\DevAI;

use PetualAI\SDK\IObject;
use PetualAI\Util\DateUtil;

/**
 * The brain!
 */
class Brain extends IObject {

	/**
	 * @var Interaction $previous_interaction
	 */
	protected $previousActualInteraction;

	/**
	 * @var Interaction $previousCompoundInteraction
	 */
	protected $previousCompoundInteraction;

	/**
	 * @var Environment
	 */
	protected $environment;

	/**
	 * @var Run
	 */
	protected $run;

	protected $thoughtCounter = 0;

	/**
	 * Set this to be the max number of loops it should try before aborting
	 * @var integer
	 */
	const MAX_THOUGHT_COUNTER = 25;

	/**
	 * @param Environment $environment
	 * @return $this
	 */
	public function setEnvironment($environment) {
		$this->environment = $environment;
		return $this;
	}

	/**
	 * This starts the thought process.
	 * It loops running thought after thought until either (1) the environment hits a stop state OR (2) we hit a $maxThoughtCounter threshold
	 */
	public function startThinking() {
		$this->log->debug('startThinking start - timestamp = '.DateUtil::getCurrentDateTimeUTC()->getTimestamp());
		$this->log->debug('startThinking environment = '.json_encode($this->environment));
		$this->setupConnection('neo4j');
		$this->run = new Run();
		$this->run->setConnection($this->connection);
		$this->run->initializeNew();
		$this->run->insert();
		$this->thoughtCounter = 0;
		$this->start();
		$this->thoughtCounter++;
		while ($this->environment->isActive() && $this->thoughtCounter < self::MAX_THOUGHT_COUNTER) {
			$this->log->debug('call think');
			$this->think();
			$this->thoughtCounter++;
		}
		$this->run->markCompleted();
		$this->environment->linkRunToObject($this->run);
		$this->log->debug('startThinking done - timestamp = '.DateUtil::getCurrentDateTimeUTC()->getTimestamp());
	}

	/**
	 * @return Run
	 */
	public function getRun() {
		return $this->run;
	}

	/**
	 * Always call start first
	 */
	protected function start() {
		$this->log->debug('start 1');
		$intendedInteraction = new Interaction();
		$intendedInteraction->setConnection($this->connection);
		$intendedInteraction->initializeForStartForActionClass($this->environment->getActionClassName());
		$this->log->debug('start 2 - $intendedInteraction = '.$intendedInteraction->toJSON());
		$actualInteraction = $this->runExperimentInteraction($intendedInteraction);
		$this->log->debug('start 4 - $actualInteraction = '.$actualInteraction->toJSON());
		$experiment = new Experiment();
		$experiment->setConnection($this->connection);
		$experiment->initializeForInteractions($intendedInteraction, $actualInteraction, $this->thoughtCounter);
		$this->run->addExperiment($experiment);
	}

	protected function think() {
		$this->log->debug('think 1 - thoughtCounter = '.$this->thoughtCounter);
		$this->log->debug('think 1 - previousActualInteraction = '.json_encode($this->previousActualInteraction));
		$this->log->debug('think 1 - previousCompoundInteraction = '.json_encode($this->previousCompoundInteraction));
		$intendedInteraction = $this->chooseInteractionToRun();
		$this->log->debug('think 2 - $intendedInteraction = '.$intendedInteraction->toJSON());
		if (isset($intendedInteraction) && $intendedInteraction->hasID()) {
			$this->log->debug('think 3');
			$actualInteraction = $this->runExperimentInteraction($intendedInteraction);
			$this->log->debug('think 4 - $actualInteraction = '.$actualInteraction->toJSON());
			$experiment = new Experiment();
			$experiment->setConnection($this->connection);
			$experiment->initializeForInteractions($intendedInteraction, $actualInteraction, $this->thoughtCounter);
			$this->run->addExperiment($experiment);
		} else {
			$this->environment->deActivate();
		}
	}

	/**
	 * If we have an experiment to test then run it and learn from its actual result
	 * Returns the actual interaction that ran for logging
	 * @param Interaction $interactionToRun
	 * @return Interaction
	 */
	protected function runExperimentInteraction($interactionToRun) {
		$this->log->debug('runExperimentInteraction - $interactionToRun = '.$interactionToRun->toJSON());
		$actualInteraction = $this->runIntendedInteractionRecursively($interactionToRun);
		$this->log->debug('runExperimentInteraction - $actualInteraction = '.$actualInteraction->toJSON());
		$this->learnInteractions($actualInteraction);
		return $actualInteraction;
	}

	/**
	 * @return string[]
	 */
	protected function getCurrentPreInteractionIDs() {
		$this->log->debug('getCurrentPreInteractionIDs - previousActualInteraction, previousCompoundInteraction = '.json_encode($this->previousActualInteraction).' ---- '.json_encode($this->previousCompoundInteraction));
		$preInteractions = array('0');
		if (isset($this->previousActualInteraction) && isset($this->previousActualInteraction->id) && $this->previousActualInteraction->isBaseAction()) {
			array_push($preInteractions, $this->previousActualInteraction->id);
		}
		if (isset($this->previousActualInteraction) && $this->previousActualInteraction->hasPreInteraction()) {
			array_push($preInteractions, $this->previousActualInteraction->pre_interaction_id);
			array_push($preInteractions, $this->previousActualInteraction->post_interaction_id);
		}
		if (isset($this->previousCompoundInteraction) && isset($this->previousCompoundInteraction->id)) {
			array_push($preInteractions, $this->previousCompoundInteraction->id);
		}
		return $preInteractions;
	}

	protected function chooseInteractionToRun() {
		$this->log->debug('chooseInteractionToRun 1');
		$currentPreInteractionIDs = $this->getCurrentPreInteractionIDs();
		$this->log->debug('chooseInteractionToRun 2 - $currentPreInteractionIDs = '.json_encode($currentPreInteractionIDs));
		$interactionToRun = new Interaction();
		$interactionToRun->setConnection($this->connection);
		$this->log->debug('chooseInteractionToRun 3');
		$interactionToRun->initializeInteractionToRun($currentPreInteractionIDs, $this->environment->getActionClassName());
		$this->log->debug('chooseInteractionToRun 4 - $interactionToRun = '.json_encode($interactionToRun));
		return $interactionToRun;
	}

	/**
	 * Recursively call all pre and post interactions all the way down to base action/rewards
	 * @param Interaction $intendedInteraction
	 */
	protected function runIntendedInteractionRecursively($intendedInteraction) {
		$this->log->debug('runIntendedInteractionRecursively - LOOOOOOP - $intendedInteraction = '.json_encode($intendedInteraction));
		if ($intendedInteraction->isBaseAction()) {
			$this->log->debug('runIntendedInteractionRecursively - isBaseAction');
			$result = $this->environment->setAction($intendedInteraction->action())->run()->getResult();
			$this->log->debug('runIntendedInteractionRecursively - result = '.$result);
			$actualInteraction = new Interaction();
			$actualInteraction->setConnection($this->connection);
			$actualInteraction->tryInitializeForActionAndResult($intendedInteraction->action(), $result);
			return $actualInteraction;
		} else {
			$this->log->debug('runIntendedInteractionRecursively - NOT base Action - call runIntendedInteractionRecursively');
			$actualPreInteraction = $this->runIntendedInteractionRecursively($intendedInteraction->preInteraction());
			$this->log->debug('runIntendedInteractionRecursively - NOT base Action - FINISH FINISH - $actualPreInteraction = '.$actualPreInteraction->toJSON());
			if ($intendedInteraction->preInteraction()->id !== $actualPreInteraction->id) {
				$this->log->debug('runIntendedInteractionRecursively - no match');
				return $actualPreInteraction; //actual did not match intended
			} else{
				$this->log->debug('runIntendedInteractionRecursively - match');
				$actualPostInteraction = $this->runIntendedInteractionRecursively($intendedInteraction->postInteraction());
				$compoundInteraction = new Interaction();
				$compoundInteraction->setConnection($this->connection);
				$compoundInteraction->tryInitializeForPreAndPost($actualPreInteraction, $actualPostInteraction);
				$this->log->debug('runIntendedInteractionRecursively - return $compoundInteraction'.$compoundInteraction->toJSON());
				return $compoundInteraction;
			}
		}
	}

	/**
	 * Add new compound interactions or update the counts
	 * 1 - previousInteraction + actualInteraction
	 * 2 - previousCompountInteraction + actualInteraction
	 * 3 - previousCompoundInteraction's PreInteraction + currentCompoundInteraction(previousInteraction + actualInteraction)
	 * Then update previous properties
	 */
	protected function learnInteractions($actualInteraction){
		$this->log->debug('learnInteractions start$actualInteraction  = '.$actualInteraction->toJSON());
		$currentCompoundInteraction = new Interaction();
		$currentCompoundInteraction->setConnection($this->connection);
		if ($this->previousActualInteraction !== null) {
			$this->log->debug('learnInteractions - 1 previousActualInteraction = '.$this->previousActualInteraction->toJSON());
			$this->log->debug('learnInteractions - 1 actualInteraction = '.$actualInteraction->toJSON());
			$currentCompoundInteraction->initializeForPreAndPost($this->previousActualInteraction, $actualInteraction);
			$currentCompoundInteraction->upsert();
			$this->log->debug('learnInteractions - 1 $currentCompoundInteraction result = '.$currentCompoundInteraction->toJSON());
		}

		if ($this->previousCompoundInteraction !== null) {
			$newInteraction = new Interaction();
			$newInteraction->setConnection($this->connection);
			$this->log->debug('learnInteractions - 2 previousCompoundInteraction = '.$this->previousCompoundInteraction->toJSON());
			$newInteraction->initializeForPreAndPost($this->previousCompoundInteraction, $actualInteraction);
			$newInteraction->upsert();
			$this->log->debug('learnInteractions - 2 = '.$currentCompoundInteraction->toJSON());
		}

		if ($this->previousCompoundInteraction !== null && $this->previousCompoundInteraction->hasPreInteraction() && $currentCompoundInteraction->hasPreInteraction()) {
			$this->log->debug('learnInteractions - 3 preInteraction = '.$this->previousCompoundInteraction->toJSON());
			$newInteraction = new Interaction();
			$newInteraction->setConnection($this->connection);
			$preInteraction = $this->previousCompoundInteraction->preInteraction();
			$this->log->debug('learnInteractions - 3 preInteraction = '.$preInteraction->toJSON());
			$newInteraction->initializeForPreAndPost($preInteraction, $currentCompoundInteraction);
			$newInteraction->upsert();
			$this->log->debug('learnInteractions - 3 = '.$currentCompoundInteraction->toJSON());
		}

		$this->log->debug('learnInteractions - update previousActualInteraction = '.$actualInteraction->toJSON());
		$this->log->debug('learnInteractions - update previousCompoundInteraction = '.$currentCompoundInteraction->toJSON());
		$this->previousActualInteraction = $actualInteraction;
		$this->previousCompoundInteraction = $currentCompoundInteraction;
		$this->log->debug('learnInteractions - update previousCompoundInteraction after = '.$this->previousCompoundInteraction->toJSON());
	}

}