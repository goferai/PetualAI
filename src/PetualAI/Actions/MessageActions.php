<?php

namespace PetualAI\SDK\Actions;

use PetualAI\SDK\Messages\Message;

class MessageActions extends IEnvironmentAction {

	/**
	 * @var Message $mainObject
	 */
	public $mainObject;

	protected function initializeMainObject() {
		$this->mainObjectKey = 'message';
		$this->mainObject = $this->environment->getVariable($this->mainObjectKey);
	}

	public static function getMainObjectClass() {
		return Message::class;
	}

}