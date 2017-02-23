<?php

namespace PetualAI\Exceptions;

/**
 * The user is authorized but what they are trying to do is a forbidden action entirely
 */
class ForbiddenException extends \Exception {
    
	public function __construct($message) {
		$message = (isset($message)) ? $message : "Forbidden";
		parent::__construct($message, 403);
	}
}

