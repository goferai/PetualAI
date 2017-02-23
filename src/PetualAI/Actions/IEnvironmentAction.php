<?php

namespace PetualAI\SDK\Actions;

use PetualAI\SDK\Environment;
use PetualAI\SDK\Run;

abstract class IEnvironmentAction {

	/**
	 * @var Environment $environment
	 */
	protected $environment;

	public $mainObjectKey;
	public $mainObject;

	/**
	 * @param Environment $environment
	 */
	public function __construct($environment) {
		$this->environment = $environment;
		$this->initializeMainObject();
	}

	abstract protected function initializeMainObject();
	abstract public static function getMainObjectClass();

	protected function updateEnvironmentVariable() {
		$this->environment->setVariable($this->mainObjectKey, $this->mainObject);
	}

	/**
	 * Runs the method in the current class if found - otherwise calls the method on the mainObject.
	 * @param string $function
	 */
	public function run($function) {
		if (method_exists($this, $function)) {
			$result = $this->$function();
		} else {
			$result = $this->mainObject->$function();
		}
		$this->updateEnvironmentVariable();
		return $result;
	}

	/**
	 * To link this run with the object it was about. Override if the id comes from something else.
	 * @param Run $run
	 */
	public function linkRunToObject($run) {
		$run->linkRunToObject($this->mainObjectKey, $this->mainObject->getID());
	}

	/**
	 * All action classes have a start method that does nothing but return true
	 * Useful for creating a compound interaction for starting out
	 */
	public function start() {
		return true;
	}

	/**
	 * All action classes have a stop method that can stop processing on the environment
	 */
	public function stop() {
		$this->environment->deActivate();
		return true;
	}

}