<?php

namespace PetualAI\Exceptions;

/**
 * The user is authorized to perform the action but they sent a bad request (probably with incorrect data). They need to resend.
 */
class BadRequestException extends \Exception {
    
	public function __construct() {
		parent::__construct("Bad Request", 400);
	}
}

