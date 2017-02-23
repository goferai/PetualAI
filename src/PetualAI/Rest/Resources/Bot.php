<?php

namespace PetualAI\Rest\Resources;

use PetualAI\SDK\Action;
use PetualAI\SDK\Services\EmailService;
use PetualAI\SDK\Services\EmailServiceOptions;
use PetualAI\Util\WebServiceUtil;
use PetualAI\Exceptions\BadRequestException;
use PetualAI\Util\ObjectUtil;
use PetualAI\SDK\Interaction;
use PetualAI\SDK\Brain;
use PetualAI\Util\EmailUtil;
use PetualAI\Util\TestingUtil;
use PetualAI\SDK\Environment;
use PetualAI\SDK\Run;
use PetualAI\SDK\Experiment;

class Bot extends IResource {

	protected function definePaths() {
		$this->paths = array(
			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/actionClasses', 				'function'=>'getActionClasses', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),

			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/actions', 						'function'=>'getActions', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'post', 	'pattern'=>'/api/bots/(\d+)/actions', 						'function'=>'createAction', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'patch', 	'pattern'=>'/api/bots/(\d+)/actions/([a-z0-9_-]+)', 		'function'=>'updateAction', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),

			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/interactions', 					'function'=>'getInteractions', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/interactions/([a-z0-9_-]+)', 	'function'=>'getSingleInteraction', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'post', 	'pattern'=>'/api/bots/(\d+)/interactions', 					'function'=>'createInteraction', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'patch', 	'pattern'=>'/api/bots/(\d+)/interactions/([a-z0-9_-]+)', 	'function'=>'updateInteraction', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),

			array('method'=>'post', 	'pattern'=>'/api/bots/(\d+)/startThinking', 				'function'=>'startThinking', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'post', 	'pattern'=>'/api/bots/(\d+)/trainActions', 					'function'=>'trainActions', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),

			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/runs', 							'function'=>'getRunList', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/runs/([a-z0-9_-]+)', 			'function'=>'getRun', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'patch', 		'pattern'=>'/api/bots/(\d+)/runs/([a-z0-9_-]+)/experiments/([a-z0-9_-]+)', 		'function'=>'fixRunExperiment', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),

			array('method'=>'get', 		'pattern'=>'/api/bots/(\d+)/experiments', 					'function'=>'getExperiments', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'post', 	'pattern'=>'/api/bots/(\d+)/experiments', 					'function'=>'createExperiment', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP),
			array('method'=>'delete', 	'pattern'=>'/api/bots/(\d+)/experiments/([a-z0-9_-]+)', 	'function'=>'deleteExperiment', 	'requireUser'=>true, 'requireUserGroup'=>STORMPATH_ADMINS_GROUP)
		);
	}

	public function startThinking() {
		$this->log->debug('startThinking 1');
		EmailUtil::$fakeSendEmailsForTesting = true;
		$this->log->debug('startThinking 2');
		TestingUtil::emptyTablesForAutoMeeting();
		$this->log->debug('startThinking 3');
		$emailID = '093c322c-357b-449f-a099-02d5475da1ea';
		TestingUtil::moveTestData('test_gofer_emails', 'srvc_gofer_emails', array('email_id'=>$emailID));
		$this->log->debug('startThinking 4');
		$environment = new Environment();

        $emailService = new EmailService();
        $email = $emailService->get((new EmailServiceOptions())->setEmailId($emailID));
        if (!$email) throw new NotFoundException();

		$environment->setVariable('email', $email);
		$this->log->debug('startThinking 5');
		$brain = new Brain();
		$brain->setEnvironment($environment)->startThinking();
		$this->log->debug('startThinking 6');
		$this->returnSuccess($brain->getRun()->toJSON());
	}

	/**
	 * Builds up interactions in reserve like this: given actions A->B->C->D this will reinforce pre and post interactions like the following
	 * 1 = C->D
	 * 2 = B->1
	 * 3 = A->2
	 * @throws BadRequestException
	 */
	public function trainActions() {
		$this->log->debug('trainActions start');
		$data = WebServiceUtil::getRequestBody();
		if (!is_array($data)) throw new BadRequestException();
		$newInteractionsToMake = [];
		$reverseActions = array_reverse($data);
		$postInteraction = null;
		foreach ($reverseActions as $newAction) {
			if (!isset($newAction->id) || !isset($newAction->result)) throw new BadRequestException();
			$preInteraction = new Interaction();
			$preInteraction->tryInitializeForActionAndResult($newAction, $newAction->result);
			if ($postInteraction !== null) {
				$compoundInteraction = new Interaction();
				$compoundInteraction->tryInitializeForPreAndPost($preInteraction, $postInteraction);
				$this->log->debug('about to make compound interaction - '.json_encode($compoundInteraction));
				array_push($newInteractionsToMake, $compoundInteraction);
				$postInteraction = $compoundInteraction;
			} else {
				$postInteraction = $preInteraction;
			}
		}
		$experimentsMade = [];
		foreach ($newInteractionsToMake as $newInteraction) {
			$newInteraction->upsert();
			$experiment = new Experiment();
			$experiment->initializeForInteractions($newInteraction, $newInteraction);
			$this->log->debug('about to make experiment - '.json_encode($experiment));
			$experiment->insert();
			array_push($experimentsMade, $experiment);
		}
		$results = new \stdClass();
		$results->interactions = $newInteractionsToMake;
		$results->experiments = $experimentsMade;
		$this->returnSuccess(json_encode($results));
	}

	/*
	 * Runs
	 */
	public function getRunList() {
		$this->log->debug('getRunList start');
		$runSDK = new Run();
		$runs = $runSDK->getRuns();
		$this->returnSuccess(json_encode($runs));
	}

	public function getRun() {
		$this->log->debug('getRun start');
		$run = new Run();
		$run->initializeForID($this->urlParts['runs']);
		$this->returnSuccess($run->toJSON());
	}

	/**
	 * This will correct whatever experiment was run in the thoughts of a run. And it will delete any other experiments in the run AFTER it (based on the order).
	 */
	public function fixRunExperiment() {
		$run = new Run();
		$run->initializeForID($this->urlParts['runs'], false);
		$run->fixExperiment($this->urlParts['experiments'], WebServiceUtil::getRequestBody());
		$this->returnSuccess($run->toJSON());
	}

	/*
	 * Actions
	 */
	public function getActionClasses() {
		$classes = [
			'\PetualAI\SDK\Actions\EmailActions',
			'\PetualAI\SDK\Actions\MessageActions'
		];
		$this->returnSuccess(json_encode($classes));
	}

    public function getActions() {
    	$this->log->debug('getActions start');
    	$actionSDK = new Action();
    	$search = (isset($_GET['search'])) ? $_GET['search'] : null ;
    	$className = (isset($_GET['className'])) ? $_GET['className'] : null ;
    	$this->log->debug('search = '.$search);
    	$actions = $actionSDK->getActions($search, WebServiceUtil::getPaginationSkip(0), WebServiceUtil::getPaginationLimit(25), $className);
    	$this->returnSuccess(json_encode($actions));
    }

    public function createAction() {
    	$this->log->debug('createAction start');
    	$data = WebServiceUtil::getRequestBody();
    	if (!isset($data->class_name) || !isset($data->function)) throw new BadRequestException();
    	$data = ObjectUtil::getClonedObjectWithProperties($data, ['class_name', 'function']);
    	$action = new Action();
    	$action->initializeNew();
    	$action->initializeForData($data);
    	$action->insert();
    	$this->returnSuccess($action->toJSON());
    }

    public function updateAction() {
    	$this->log->debug('updateAction start');
    	$data = WebServiceUtil::getRequestBody();
    	if (!isset($data->id) || !isset($data->class_name) || !isset($data->function)) throw new BadRequestException();
    	$action = new Action();
    	$action->initializeForData($data);
    	$action->update();
    	$this->returnSuccess($action->toJSON());
    }


    /*
     * Interactions
     */

    public function getInteractions() {
    	$this->log->debug('getInteractions start');
    	$interactionSDK = new Interaction();
    	$search = (isset($_GET['search'])) ? $_GET['search'] : null ;
    	$this->log->debug('search = '.$search);
    	$params = [
    			'search'=>$search,
    			'skip'=>WebServiceUtil::getPaginationSkip(0),
    			'limit'=>WebServiceUtil::getPaginationLimit(100)
    	];
    	if (isset($_GET['className'])) {
    		$params['className'] =$_GET['className'];
    	}
    	$interactions = $interactionSDK->getInteractions($params);
    	$this->returnSuccess(json_encode($interactions));
    }

    public function getSingleInteraction() {
    	$this->log->debug('getInteractions start - id = '.$this->urlParts['interactions']);
    	$interaction = new Interaction();
    	$interaction->initializeForID($this->urlParts['interactions']);
    	$this->returnSuccess($interaction->toJSON());
    }

    public function createInteraction() {
    	$this->log->debug('createInteraction start');
    	$data = WebServiceUtil::getRequestBody();
    	if (!isset($data->action_id) && !isset($data->pre_interaction_id)) throw new BadRequestException();
    	$data = ObjectUtil::getClonedObjectWithProperties($data, ['action_id', 'result', 'reward', 'counter', 'pre_interaction_id', 'post_interaction_id']);
    	$interaction = new Interaction();
    	$interaction->initializeNew();
    	$interaction->initializeForData($data);
    	$interaction->upsert();
    	$this->returnSuccess($interaction->toJSON());
    }

    public function updateInteraction() {
    	$this->log->debug('updateInteraction start');
    	$data = WebServiceUtil::getRequestBody();
    	if (!isset($data->action_id) && !isset($data->pre_interaction_id)) throw new BadRequestException();
    	$interaction = new Interaction();
    	$interaction->initializeForData($data);
    	$interaction->upsert();
    	$this->returnSuccess($interaction->toJSON());
    }



    /*
     * Experiments
     */
    public function deleteExperiment() {
    	$this->log->debug('deleteExperiment start');
    	$experiment = new Experiment();
    	$experiment->id = $this->urlParts['experiments'];
    	$experiment->delete();
    	$this->returnSuccess("");
    }

    public function getExperiments() {
    	$this->log->debug('getExperiments start');
    	$experimentSDK = new Experiment();
    	$experiments = $experimentSDK->getExperiments(null, WebServiceUtil::getPaginationSkip(0), WebServiceUtil::getPaginationLimit(25));
    	$this->returnSuccess(json_encode($experiments));
    }

    public function createExperiment() {
    	$this->log->debug('createExperiment start');
    	$data = WebServiceUtil::getRequestBody();
    	$this->log->debug('createExperiment $data = '.json_encode($data));
    	if (!isset($data->intended_interaction_id) && !isset($data->actual_interaction_id)) throw new BadRequestException();
    	$data = ObjectUtil::getClonedObjectWithProperties($data, ['intended_interaction_id', 'actual_interaction_id']);
    	$experiment = new Experiment();
    	$experiment->initializeNew();
    	$experiment->initializeForData($data);
    	$experiment->insert();
    	$this->returnSuccess($experiment->toJSON());
    }
}