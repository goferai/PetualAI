<?php

use Gofer\Apps\Gofer\GoferOpenWebsiteAction;
use Gofer\Util\TestingUtil;

class GoferOpenWebsiteActionTest extends PHPUnit_Framework_TestCase
{
    public function testExecuteHttp()
    {
        // Arrange
        $originalURL = "http://google.com";
        $message = TestingUtil::buildMessage(
            TestingUtil::$mainTestUserID,
            ['url' => $originalURL]
        );
        $action = new GoferOpenWebsiteAction($message);
        $messageResponseList = $action->run();
        $expectedPlanAction = new \Gofer\Messages\Actions\OpenWebsite($originalURL);
        $this->assertEquals(1,count($messageResponseList->count()));
        $this->assertEquals(json_encode($expectedPlanAction), json_encode($messageResponseList->first()->getAction()));
    }
    
    public function testExecuteHttps()
    {
    	// Arrange
    	$originalURL = "https://google.com";
    	$message = TestingUtil::buildMessage(
            TestingUtil::$mainTestUserID,
            ['url' => $originalURL]
        );
    	$action = new GoferOpenWebsiteAction($message);
        $messageResponseList = $action->run();
    	$expectedPlanAction = new \Gofer\Messages\Actions\OpenWebsite($originalURL);
        $this->assertEquals(1,count($messageResponseList->count()));
    	$this->assertEquals(json_encode($expectedPlanAction), json_encode($messageResponseList->first()->getAction()));
    }
    
    public function testExecuteWWW()
    {
    	$originalURL = "www.google.com";
    	$message = TestingUtil::buildMessage(
            TestingUtil::$mainTestUserID,
            ['url' => $originalURL]
        );
    	$action = new GoferOpenWebsiteAction($message);
        $messageResponseList = $action->run();
    	$expectedPlanAction = new \Gofer\Messages\Actions\OpenWebsite("http://$originalURL");
        $this->assertEquals(1,count($messageResponseList->count()));
        $this->assertEquals(json_encode($expectedPlanAction), json_encode($messageResponseList->first()->getAction()));
    }

    public function testExecuteBasicURL()
    {
    	// Arrange
    	$originalURL = "google.com";
    	$message = TestingUtil::buildMessage(
            TestingUtil::$mainTestUserID,
            ['url' => $originalURL]
        );
    	$action = new GoferOpenWebsiteAction($message);
        $messageResponseList = $action->run();
    	$expectedPlanAction = new \Gofer\Messages\Actions\OpenWebsite("http://$originalURL");
        $this->assertEquals(1,count($messageResponseList->count()));
        $this->assertEquals(json_encode($expectedPlanAction), json_encode($messageResponseList->first()->getAction()));
    }

}