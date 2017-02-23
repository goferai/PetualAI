<?php

namespace PetualAI\Rest\Resources;

use PetualAI\Rest\RouteParameters;
use PetualAI\SDK\Services\Email;
use PetualAI\SDK\Services\EmailBuilder;
use PetualAI\SDK\Services\EmailService;
use PetualAI\Util\Auth\CurrentUser;
use PetualAI\Util\EmailUtil;
use \PetualAI\Util\Log;
use PetualAI\Util\ObjectUtil;
use PetualAI\Util\StringUtil;

abstract class IResource {

    /**
     * @var array
     */
    public $paths;

    /**
     * @var Log
     */
    protected $log;

    /**
     * The user who made the rest call - (if known)
     * @var \PetualAI\SDK\Services\User
     */
    protected $user;

    /**
     * @var array
     */
    protected $urlParts;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    protected $function;
    
    public function __construct() {
    	$this->log = new Log(basename(__FILE__).'|'.ObjectUtil::getObjectsShortClassName($this));
        $this->definePaths();
    }
    
    protected abstract function definePaths();

    /**
     * @param RouteParameters $routeParameters
     */
    public function run($routeParameters) {
    	$this->user = CurrentUser::getInstance()->user();
    	$this->uri = $routeParameters->getUri();
    	$this->function = $routeParameters->getFunction();
    	$this->urlParts = $routeParameters->getUriParts();
        $function = $this->function;
    	$this->$function();
    }

    /**
     * Returns a 200 OK response
     * @param string $responseBody
     * @param string $contentType Optional. Pass in what the header output type is going to be. Defaults to application/json
     */
    protected function returnSuccess($responseBody, $contentType = 'application/json') {
    	$this->log->debug("returnSuccess = ".$responseBody);
    	header('HTTP/1.0 200 OK');
    	header("Content-Type: $contentType");
    	echo $responseBody;
    }

    /**
     * Returns a 200 OK response with the contents of a file.
     * Make sure the file exists before calling this
     * @param string $filePath
     */
    protected function returnFileSuccess($filePath) {
        $mimeType = mime_content_type($filePath);
        header('HTTP/1.0 200 OK');
        header("Content-Type: $mimeType");
        readfile($filePath);
    }
    
    /**
     * Returns a 201 Created response
     */
    protected function returnCreated() {
    	$this->log->debug("returnCreated");
    	header('HTTP/1.0 201 Created');
    }

    /**
     * @param \Exception $e
     * @param string $statusCode
     * @param string $errorCode
     * @param string $errorMessage
     */
    protected function returnError($e = null, $statusCode = 'HTTP/1.0 500 Internal Server Error', $errorCode = "Invalid Request", $errorMessage = null) {
    	$this->log->error("Error = ", $e);
    	$errorMessage = (isset($errorMessage)) ? $errorMessage : ((isset($e)) ? $e->getMessage() : "Unknown Error" );
    	header($statusCode);
    	header('Content-Type: application/json');
    	echo '{"errorCode" : "'.$errorCode.'", "error" :"'.$errorMessage.'"}';
    }
    
    protected function getIDArray() {
    	$ids = array();
    	foreach($this->urlParts as $key=>$value) {
    		if (isset($value)) {
    			array_push($ids, StringUtil::pluralToSingular($key)."_id");
    		}
    	}
    	return $ids;
    }
    
    protected function getTableName() {
    	$tableName = "srvc";
    	foreach (array_keys($this->urlParts) as $key) {
    		$tableName .= "_".$key;
    	}
    	return $tableName;
    }

}