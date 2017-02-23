<?php

namespace PetualAI\Exceptions;

class NotFoundException extends \Exception {
    
	public function __construct() {
		parent::__construct("Not found", 404);
	}
}

