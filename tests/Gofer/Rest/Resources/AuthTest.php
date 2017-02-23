<?php

use Gofer\Rest\Resources\Auth;
use Gofer\Util\TestingUtil;
use Gofer\Util\WebServiceUtil;

/** @noinspection PhpUndefinedClassInspection */
class AuthTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;
	
	public function test_forgot_bad_email() {
        WebServiceUtil::$testingSimulatedRequestBody = '{"email":"ZAP%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s%n%s\n"}';
    	$this->expectOutputRegex('/.*Email is not a valid format.*/');
    	TestingUtil::mockRouterCall(null, WebServiceUtil::METHOD_POST, '/api/auth/forgot');
    }

    /**
     * @dataProvider checkEmailProvider
     * @param $email
     * @param $expectedResult
     * @param $expectedReason
     */
    public function test_checkEmail($email, $expectedResult, $expectedReason) {
        $this->expectOutputString('{"Result":"'.$expectedResult.'", "Reason":"'.$expectedReason.'"}');
        TestingUtil::mockRouterCall(null, WebServiceUtil::METHOD_GET, '/api/auth/checkEmail?e='.rawurlencode($email));
    }

    public function checkEmailProvider() {
        return [
            [TestingUtil::$mainTestUserEmail, 'True', ''],
            ['somenewemail320932r@gofer.co', 'False', Auth::CHECK_EMAIL_REASON_NEW_USER],
            ['authtest.checkemail@gofer.co', 'False', Auth::CHECK_EMAIL_REASON_EMAIL_VERIFICATION],
        ];
    }
    
}