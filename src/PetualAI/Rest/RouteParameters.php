<?php

namespace PetualAI\Rest;


use PetualAI\Exceptions\NotFoundException;
use PetualAI\SDK\Services\User;

class RouteParameters {

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
	protected $uri;

    /**
     * @var string
     */
	protected $function;

    /**
     * @var array
     */
	protected $uriParts;

    /**
     * @var string
     */
    protected $method;

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri) {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunction() {
        return $this->function;
    }

    /**
     * @param string $function
     * @return $this
     */
    public function setFunction($function) {
        $this->function = $function;
        return $this;
    }

//    /**
//     * @return array
//     */
//    public function getUrlParts() {
//        return $this->urlParts;
//    }

//    /**
//     * @param array $urlParts
//     * @return $this
//     */
//    public function setUrlParts($urlParts) {
//        $this->urlParts = $urlParts;
//        return $this;
//    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }

    /**
     * Checks that the uri is valid. If not throws a NotFoundException.
     * @throws NotFoundException
     */
    public function validateUri() {
        if (strlen($this->uri) < 6) throw new NotFoundException();
        if (substr($this->uri,0,5) !== '/api/') throw new NotFoundException();
        $parts = explode("/", substr($this->uri, 1));
        if ($parts < 2) throw new NotFoundException();
    }

    /**
     * Returns the uri parts as an array of values
     * @return array
     */
    public function getUriPaths() {
        return explode("/", substr($this->uri,1));
    }

    /**
     * Returns uri as key value pairs
     * NOTE: This builds the parts the first time it is called. After that it just returns the same result without re-calculating
     * @return array
     */
    public function getUriParts() {
        if (!empty($this->uriParts)) return $this->uriParts;
        $uriParts = array();
        $uriCleaned = str_replace("/api/","",$this->uri);
        $parts = explode("/", $uriCleaned);
        $key = null;
        $keyRow = true;
        foreach($parts as $part) {
            if ($keyRow) {
                $key = $part;
            } else {
                $uriParts[$key] = $part;
                $key = null;
            }
            $keyRow = ($keyRow) ? false : true;
        }
        if (isset($key)) {
            $uriParts[$key] = null;
        }
        $this->uriParts = $uriParts;
        return $this->uriParts;
    }

}

