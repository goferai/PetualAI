<?php

namespace PetualAI\Exceptions;

/**
 * To be used to tell the user they do not have the authority or are not authenticated to do this task
 */
class NotAuthorizedException extends \Exception {
    
	public function __construct() {
		parent::__construct("Not authorized", 401);
	}
}

