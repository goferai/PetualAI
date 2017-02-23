<?php

use Gofer\Util\WebServiceUtil;

class WebServiceUtilTest extends PHPUnit_Framework_TestCase
{
    public function test_callService_GET_JSON()
    {
    	$url = 'https://api.wit.ai/message?v=20151026&q=take%20a%20screenshot&context=%7B%22timezone%22%3A%22America%5C%2FLos_Angeles%22%7D';
    	$jsonResponse = WebServiceUtil::callService($url, "GET", "", array("Authorization" => "Bearer 56D3UIVSP7BA5NAQMHQDCCLXDDIZROWL"));
    	$this->assertContains('gofer_screenshot', $jsonResponse);
    }
}