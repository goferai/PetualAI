<?php
$_SERVER['DOCUMENT_ROOT'] = "/home/www-gofer-server/gofer-server/src";
/** @noinspection PhpIncludeInspection */
require '/home/www-gofer-server/gofer-server/src/vendor/autoload.php';
/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/../configuration.php';
date_default_timezone_set('UTC');       // Set the default timezone