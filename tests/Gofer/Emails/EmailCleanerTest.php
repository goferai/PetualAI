<?php

use Gofer\Emails\EmailCleaner;
use Gofer\SDK\Services\EmailService;
use Gofer\SDK\Services\EmailServiceOptions;
use Gofer\Util\TestingUtil;

class EmailCleanerTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        $emailIds = [
            '122c322c-357b-449f-a099-02d5475da2bb',
            '413c322c-357b-449f-a099-02d5475da1ac'
        ];
        TestingUtil::emptyTables('srvc_emails');
        TestingUtil::moveTestData('test_emails', 'srvc_emails', array('email_id' => $emailIds));
    }

    public function testStripSignatureAndQuotedEmail_SimpleTextSignature() {
        $id = '122c322c-357b-449f-a099-02d5475da2bb';
        $emailService = new EmailService();
        $email = $emailService->get((new EmailServiceOptions())->setEmailId($id));
        $email->getFromContact();
    	$emailCleaner = new EmailCleaner($email);
    	$emailCleaner->clean()->stripSignatureAndQuotedEmail();
    	$this->assertNotContains("Ryan", $emailCleaner->getCleanText());
    }
    
    public function testStripSignatureAndQuotedEmail_ComplexTextSignature() {
        $id = '413c322c-357b-449f-a099-02d5475da1ac';
        $emailService = new EmailService();
        $email = $emailService->get((new EmailServiceOptions())->setEmailId($id));
    	$emailCleaner = new EmailCleaner($email);
    	$emailCleaner->clean()->stripSignatureAndQuotedEmail();
    	$this->assertNotContains("From: Chip Allen <chip@gofer.co>", $emailCleaner->getCleanText());
    	$this->assertNotContains("Ryan", $emailCleaner->getCleanText());
    }

    /**
     * @dataProvider getGoferRelatedSentencesProvider
     * @param $from
     * @param $to
     * @param $cleanText
     * @param $expected
     */
    public function test_getGoferRelatedSentences($from, $to, $cleanText, $expected) {
        $email = new \Gofer\SDK\Services\Email();
        $email->setEmailFrom($from);
        $email->setEmailTo($to);
        $email->setCleanText($cleanText);
        $emailCleaner = new EmailCleaner($email);
        $emailCleaner->setCleanText($cleanText);
        $this->assertEquals($expected, $emailCleaner->getGoferRelatedSentences());
    }

    public function getGoferRelatedSentencesProvider() {
        return [
            [
                'person@gofer.co',
                'gofer2@devmail.gofer.co',
                'It should return the whole string since it is the only TO contact. I am copying my Gofer assistant to arrange. Another sentence',
                ['It should return the whole string since it is the only TO contact. I am copying my Gofer assistant to arrange. Another sentence'],
            ],
            [
                'person@gofer.co',
                'person2@gofer.co, gofer2@devmail.gofer.co',
                'This is a sentence that should not be returned. I am copying my Gofer assistant to arrange. Another sentence',
                ['I am copying my Gofer assistant to arrange'],
            ],
        ];
    }
    
}