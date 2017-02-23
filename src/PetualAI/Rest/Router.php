<?php

namespace PetualAI\Rest;

use PetualAI\Exceptions\AccountUpgradeRequiredException;
use PetualAI\Exceptions\AppApiDisabledException;
use PetualAI\Exceptions\AppEmailNotActivatedException;
use PetualAI\Exceptions\AppNotActivatedException;
use PetualAI\Exceptions\AppNotConnectingException;
use PetualAI\Exceptions\BadRequestException;
use PetualAI\Exceptions\EmailVerificationRequiredException;
use PetualAI\Exceptions\ForbiddenException;
use PetualAI\Exceptions\NotAuthorizedException;
use PetualAI\Exceptions\NotFoundException;
use PetualAI\Rest\Resources\IResource;
use PetualAI\SDK\Services\User;
use PetualAI\SDK\Services\UserActivity;
use PetualAI\SDK\Services\UserActivityBuilder;
use PetualAI\SDK\Services\UserActivityService;
use PetualAI\Util\Auth\CurrentUser;
use PetualAI\Util\Log;
use PetualAI\Util\ObjectUtil;
use PetualAI\Util\StringUtil;
use PetualAI\Util\WebServiceUtil;

class Router {

    /**
     * @var bool
     */
	protected $testingMode = false;

    /**
     * @var Log
     */
	private $log;

	/**
	 * The resource class to find based on the api url and run
	 * @var IResource
	 */
	private $resource;

    /**
     * For storing the values for the route that was called
     * @var RouteParameters
     */
    private $routeParameters;
	
	public function __construct($testingMode = false) {
		$this->log = new Log(basename(__FILE__));
        $this->log->debug("__construct - start");
		$this->testingMode = $testingMode;
        $this->routeParameters = new RouteParameters();
		if (!$this->testingMode) {
            $this->routeParameters->setMethod(WebServiceUtil::getRequestMethod())
                                  ->setUri(WebServiceUtil::getUri());
		}
        $this->log->debug("__construct - end");
	}

    /**
     * Used only when testing to manually set the user
     * @param User $currentUser
     */
    public function setCurrentUser($currentUser) {
        CurrentUser::getInstance()->setCurrentUser($currentUser);
    }

    /**
     * Used only when testing to manually set the uri
     * @param string $uri
     */
    public function setUri($uri) {
        $this->routeParameters->setUri($uri);
    }

    /**
     * Used only when testing to manually set the method
     * @param string $method
     */
    public function setMethod($method) {
        $this->routeParameters->setMethod($method);
    }

    /**
     * The main function to call and start processing the REST call
     */
	public function route() {
		try {
			if (strtolower($this->routeParameters->getMethod()) === 'options') {
				WebServiceUtil::setCORSHeaders();
				return;
			}
			$this->routeParameters->validateUri();
			$this->buildResource();
			$this->callFunction();
			$this->log->debug("route - done");
            //TODO: Simplify this logic into a single method. Update the gofer exceptions to all derive from a parent class that we catch and handle as one.
        } catch (AccountUpgradeRequiredException $e) {
            $this->log->error("Error = ", $e);
            $this->returnAccountUpgradeRequired($e);
        } catch (AppApiDisabledException $e) {
            $this->log->error("Error = ", $e);
            $this->returnNotAuthorized($e);
        } catch (AppEmailNotActivatedException $e) {
            $this->log->error("Error = ", $e);
            $this->returnNotAuthorized($e);
        } catch (AppNotActivatedException $e) {
            $this->log->error("Error = ", $e);
            $this->returnNotAuthorized($e);
        } catch (AppNotConnectingException $e) {
            $this->log->error("Error = ", $e);
            $this->returnBadGateway($e);
        } catch (BadRequestException $e) {
            $this->log->error("Error = ", $e);
            $this->returnBadRequest($e);
        } catch (EmailVerificationRequiredException $e) {
            $this->log->error("Error = ", $e);
            $this->returnEmailVerificationRequired($e);
        } catch (ForbiddenException $e) {
            $this->log->error("Error = ", $e);
            $this->returnForbidden($e);
        } catch (NotAuthorizedException $e) {
			$this->log->error("Error = ", $e);
			$this->returnNotAuthorized($e);
		} catch (NotFoundException $e) {
			$this->log->error("Error = ", $e);
			$this->returnNotFound($e);
		} catch(\InvalidArgumentException $e) {
			$this->log->error("Error = ", $e);
			$this->returnNotAuthorized($e);
		} catch (\Exception $e) {
			$this->log->error("Error = ", $e);
			$this->returnUnknownError($e);
		}
	}

    /**
     * Creates an instance of the resource requests.
     * @throws NotFoundException
     */
	private function buildResource() {
		$paths = $this->routeParameters->getUriPaths();
		$resourceName = ucfirst($paths[1]);
		$resourceName = StringUtil::pluralToSingular($resourceName);
		$resource = '\\'.ObjectUtil::getObjectsNamespace(IResource::class).'\\'.$resourceName;
		if(!class_exists($resource)) throw new NotFoundException();
		$this->resource = new $resource();
	}

    /**
     * Find the right function in that resource and call it
     * @throws NotFoundException
     */
	private function callFunction() {
		foreach ($this->resource->paths as $path) {
			if ($path['method'] !== strtolower($this->routeParameters->getMethod())) continue;
			$matches = array();
			if (preg_match_all('#^' . $path['pattern'] . '$#', $this->routeParameters->getUri(), $matches, PREG_OFFSET_CAPTURE)) {
				if (!$this->testingMode) {
					$this->authenticateUser($path['requireUser'], $path['requireUserGroup']);
				}
				$this->checkForDelayedEmailVerification($path['requireUser']);
                //$this->tryBlockExpiredUser($path);
                $this->routeParameters->setFunction($path['function']);
                $this->logUserActivity();
				call_user_func_array(array($this->resource,'run'), [$this->routeParameters]);
				return;
			}
		}
		throw new NotFoundException();
	}

    /**
     * Checks if the route is required, the delayedEmailVerification needs to be triggered, and enough time has passed since registering.
     * @param bool $requireUser
     * @throws EmailVerificationRequiredException
     */
	private function checkForDelayedEmailVerification($requireUser) {
	    $this->log->debug('checkForDelayedEmailVerification');
        if (
            $requireUser
            && CurrentUser::getInstance()->user()->shouldTriggerEmailVerification()
            && CurrentUser::getInstance()->user()->minutesSinceRegistered() > 1440
        ) {
            $this->log->debug('triggerDelayedEmailVerification');
            CurrentUser::getInstance()->triggerDelayedEmailVerification();
            throw new EmailVerificationRequiredException();
        }
    }

    /**
     * Attempts to authenticate the user
     * Checks first for a valid user. (If the route required the user or even if it doesn't if they passed an accessToken
     * Checks second for whether user is in the required group
     * @param boolean $userRequired
     * @param boolean $userGroupNameRequired
     * @throws EmailVerificationRequiredException
     * @throws NotAuthorizedException
     */
	private function authenticateUser($userRequired, $userGroupNameRequired) {
        $this->log->debug("authenticateUser = ".json_encode($userRequired).", ".json_encode($userGroupNameRequired));
		if ($userRequired || $this->hasAccessToken()) {
            try {
                $this->log->debug("authenticateUser try authenticate");
                $accessToken = $this->getAccessToken();
                CurrentUser::getInstance()->authenticate($accessToken);
            } catch (NotAuthorizedException $e) {
                if ($userRequired) throw $e; //rethrow if user is required. Otherwise ignore since user was not required.
            }
            catch (EmailVerificationRequiredException $e) {
                if ($userRequired) throw $e; //rethrow if user is required. Otherwise ignore since user was not required.
            }
        }
        $this->log->debug("authenticateUser 2");
		if ($userGroupNameRequired && CurrentUser::getInstance()->user()->getGroupList()->hasGroup($userGroupNameRequired) === false) throw new NotAuthorizedException();
	}

    /**
     * Returns a 400
     * @param \Exception $e
     */
    private function returnBadRequest($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "Request could not be understood." ;
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json');
        echo '{"errorCode":"Bad Request", "code": 400, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
    }

    /**
     * Returns a 401
     * @param \Exception $e
     */
    private function returnNotAuthorized($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "You are not authorized for this resource" ;
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo '{"errorCode":"Unauthorized", "code": 401, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
    }

    /**
     * Returns a 402
     * @param \Exception $e
     */
	private function returnAccountUpgradeRequired($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "Account locked. Upgrade plan to enable access." ;
        header('HTTP/1.1 402 Upgrade Required');
        header('Content-Type: application/json');
        echo '{"errorCode":"Upgrade Required","code": 402, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
    }

    /**
     * Returns a 422
     * @param \Exception $e
     */
    private function returnEmailVerificationRequired($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "Email verification required. Click the verification link in your email and try again." ;
        header('HTTP/1.1 422 Email Verification Required');
        header('Content-Type: application/json');
        echo '{"errorCode":"Email Verification Required","code": 422, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
    }

    /**
     * Returns a 403
     * @param \Exception $e
     */
    private function returnForbidden($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "You are not allowed to access this resource" ;
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo '{"errorCode":"Forbidden","code": 403, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
    }

    /**
     * Returns a 404
     * @param \Exception $e
     */
	private function returnNotFound($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "Page not found" ;
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: application/json');
        echo '{"errorCode":"Not Found", "code": 404, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
	}

    /**
     * Returns a 502
     * @param \Exception $e
     */
    private function returnBadGateway($e) {
        $errorMessage = (isset($e)) ? $e->getMessage() : "The server had trouble connecting to an outside service" ;
        header('HTTP/1.1 502 Bad Gateway');
        header('Content-Type: application/json');
        echo '{"errorCode":"Bad Gateway", "code": 502, "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
    }

	/**
	 * This will catch any exceptions that are not caught in a resource and call the return error method to handle them gracefully
	 * @param \Exception $e
	 */
	private function returnUnknownError($e) {
		$errorMessage = (isset($e)) ? $e->getMessage() : "Unknown Error" ;
        $code = (!empty($e->getCode()) && is_int($e->getCode())) ? $e->getCode() : 500 ;
		header('HTTP/1.0 '.$code.' Internal Server Error');
		header('Content-Type: application/json');
		echo '{"errorCode" : "Invalid Request", "code":'.$code.', "error" :"'.$errorMessage.'", "message" :"'.$errorMessage.'"}';
	}

    /**
     * Checks if either a cookie was passed or the Authorization header
     * @return bool
     */
	private function hasAccessToken() {
        $headers = apache_request_headers();
        return (isset($_GET['token']) || isset($headers['Authorization']) || $this->hasCookieAccessToken());
    }

    /**
     * @return bool
     */
    private function hasCookieAccessToken() {
        if (!isset($headers['Cookie'])) return false;
        $parsedCookie = WebServiceUtil::parseCookieString($headers['Cookie']);
        return isset($parsedCookie['access_token']);
    }
	
	private function getAccessToken() {
		$headers = apache_request_headers();
        if(isset($_GET['token'])) {
            $this->log->debug("get token exists");
            return $_GET['token'];
        } elseif(isset($headers['Authorization'])) {
			$this->log->debug("auth header exists");
			$authHeader = $headers['Authorization'];
			$headerParts = explode(' ', $authHeader);
			return $headerParts[1];
		} else {
			$this->log->debug("cookie header exists");
			if (!isset($headers['Cookie'])) throw new NotAuthorizedException();
			$parsedCookie = WebServiceUtil::parseCookieString($headers['Cookie']);
			if (!isset($parsedCookie['access_token'])) throw new NotAuthorizedException();
			return $parsedCookie['access_token'];
		}
	}

//    /**
//     * Checks if the user is expired and blocks the request unless allowExpiredUser is set to true
//     * @param mixed $path
//     * @throws AccountUpgradeRequiredException
//     */
//    protected function tryBlockExpiredUser($path) {
//        $blockExpiredUser = (!isset($path['allowExpiredUser']) || $path['allowExpiredUser'] === false);
//        $this->log->debug("blockExpiredUser = " . json_encode($blockExpiredUser));
//        if (
//            $path['requireUser']
//            && $blockExpiredUser
//            && CurrentUser::getInstance()->user()->isTrialExpired()
//        ) {
//            throw new AccountUpgradeRequiredException();
//        }
//    }

    /**
     * Save the router call to the user activity logging
     */
    protected function logUserActivity() {
        $this->log->debug('logUserActivity - start');
        $userId = (CurrentUser::getInstance()->exists()) ? CurrentUser::getInstance()->user()->getUserId() : UserActivity::NO_USER_ID;
        $this->log->debug('logUserActivity - 1');
        $activityValue = sprintf(
            '%s %s - %s',
            strtoupper($this->routeParameters->getMethod()),
            array_keys($this->routeParameters->getUriParts())[0],
            $this->routeParameters->getFunction()
        );
        $userActivity = (new UserActivityBuilder())
            ->setUserId($userId)
            ->setActivityKey(UserActivity::ACTIVITY_API_CALL)
            ->setActivityValue($activityValue)
            ->setDetails(json_encode($this->routeParameters->getUriParts()))
            ->build();
        (new UserActivityService())->insert($userActivity);
    }

}

