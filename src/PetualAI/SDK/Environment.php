<?php

namespace PetualAI\SDK\DevAI;

use PetualAI\SDK\IObject;
use PetualAI\SDK\Actions\EmailActions;
use PetualAI\SDK\Actions\MessageActions;
use PetualAI\SDK\Actions\IEnvironmentAction;

/**
 * Represents all code that actually has to be called - not just thoughts like in the brain.
 * This object should be initialized before a thought 'process' and used throughout it - it will encapsulate all the info from thought to thought in the process
 */
class Environment extends IObject {

	/**
	 * @var int
	 */
	protected $result;

	/**
	 * @var Action
	 */
	protected $action;

	/**
	 * Holds the variables in an array of keys to variable objects
	 * @var array
	 */
	public $variables;

	/**
	 * Boolean that represents when the environment is active or finished. Once finished then the processing stops.
	 * @var boolean $active
	 */
	protected $active;

	protected function initialize() {
		$this->active = true;
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		return $this->active;
	}

	public function deActivate() {
		$this->active = false;
	}

	/**
	 * @param Action $action
	 * @return $this
	 */
	public function setAction($action) {
		$this->action = $action;
		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function setVariable($key, $value) {
		$this->variables[$key] = $value;
		return $this;
	}

	/**
	 * @param string $key
	 * @return \stdClass
	 */
	public function getVariable($key) {
		return $this->variables[$key];
	}

	public function getMainObjectClassName() {
		$key = array_keys($this->variables)[0];
		switch ($key) {
			case 'email':
				return EmailActions::getMainObjectClass(true);
				break;
			case 'message':
				return MessageActions::getMainObjectClass(true);
				break;
		}
	}

	public function getActionClassName() {
		$key = array_keys($this->variables)[0];
		$slash = '\\';
		switch ($key) {
			case 'email':
				return $slash.EmailActions::class;
				break;
			case 'message':
				return $slash.MessageActions::class;
				break;
		}
	}

	/**
	 * Run the current action
	 * @return $this
	 */
	public function run() {
		$this->log->debug("run - action = ".$this->action->toJSON());
		$environmentAction = $this->getEnvironmentAction();
		$this->log->debug("run - function = ".$this->action->function);
		$this->result = $environmentAction->run($this->action->function);
		$this->log->debug("run - this->result = $this->result");
		return $this;
	}

	/**
	 * @return IEnvironmentAction
	 */
	protected function getEnvironmentAction() {
		$class = $this->action->class_name;
		$this->log->debug("run - class = $class");
		$environmentAction = new $class($this);
		return $environmentAction;
	}

	/**
	 * Saves a link from this run to the object it was run about. Each actionClass is required to handle how it will save the link
	 * @param Run $run
	 */
	public function linkRunToObject($run) {
		$this->log->debug("linkRunToObject - environment = ".$this->toJSON());
		$environmentAction = $this->getEnvironmentAction();
		$this->log->debug("linkRunToObject - environmentAction = ".json_encode($environmentAction));
		$environmentAction->linkRunToObject($run);
	}

	/**
	 * Get the result after running an action
	 * @return int
	 */
	public function getResult() {
		return $this->convertResultToInt();
	}

	/**
	 * Converts the result of the function ran to an integer. If there was no result or it just returns an object then we call it 1
	 * @param mixed $result
	 * @return int
	 */
	protected function convertResultToInt() {
		if (!isset($this->result) || is_null($this->result)) return 1;
		if (is_int($this->result) ||is_integer($this->result)) return $this->result;
		if (is_object($this->result)
				|| is_array($this->result)
				|| is_string($this->result)) return 1;
		if (is_double($this->result)
				|| is_float($this->result)) return round($this->result);
		return intval($this->result);
	}

}