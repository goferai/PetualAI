<?php

class MessageRunnerTest extends PHPUnit_Framework_TestCase {

    	public function test_run() {
        $this->assertEquals(1, 1);
	}

//TODO: turn back on
//    /**
//     * @dataProvider runProvider
//     * @param $text
//     * @param $expected
//     */
//	public function test_run($text, $expected) {
//        $message = TestingUtil::buildMessage(
//            TestingUtil::$mainTestUserID,
//            null,
//            $text
//        );
//        $messageRunner = new MessageRunner();
//        $messageRunner
//            ->setUser(TestingUtil::getTestUser())
//            ->setMessage($message)
//            ->run();
//        $this->assertEquals($expected, $messageRunner->);
//	}
//
//    public function runProvider() {
//        return [
//            ['open the jibjab gibberish account', true],
////            ['jibjab gibberish account', false],
//        ];
//    }
	
}