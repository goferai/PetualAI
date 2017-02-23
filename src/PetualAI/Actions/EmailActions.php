<?php

namespace PetualAI\SDK\Actions;

use PetualAI\SDK\AutoMeeting\Email;

class EmailActions extends IEnvironmentAction {

	/**
	 * @var Email $mainObject
	 */
	public $mainObject;

	protected function initializeMainObject() {
		$this->mainObjectKey = 'email';
		$this->mainObject = $this->environment->getVariable($this->mainObjectKey);
	}

	public static function getMainObjectClass($addLeadingSlash = false) {
		$slash = ($addLeadingSlash === true) ? '\\' : '' ;
		$class = \PetualAI\SDK\AutoMeeting\Email::class;
		return $slash.$class;
	}

}