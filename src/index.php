<?php

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuration.php';

$router = new \Gofer\Rest\Router();
\Gofer\Util\WebServiceUtil::setCorsHeaders();
$router->route();