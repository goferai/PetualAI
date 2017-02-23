<?php

use Gofer\Slack\SlackTextCleaner;

class SlackTextCleanerTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider cleanProvider
     * @param $text
     * @param $expected
     */
    public function test_clean($text, $expected) {
        $slackTextCleaner = new SlackTextCleaner($text);
        $this->assertEquals($expected, $slackTextCleaner->clean());
    }

    public function cleanProvider() {
        return [
            ['<@U2FDFH5GT> what things can I say?', 'what things can I say?'],
        ];
    }

    /**
     * @dataProvider replaceUserMentionsWithNamesProvider
     * @param $text
     * @param $expected
     */
    public function test_replaceUserMentionsWithNames($text, $expected) {
        $slackTextCleaner = new SlackTextCleaner($text);
        $this->assertEquals($expected, $slackTextCleaner->replaceUserMentionsWithNames()->getText());
    }

    public function replaceUserMentionsWithNamesProvider() {
        return [
            ['I love <@U2FDFH5GT|john>', 'I love john'],
            ['<@U2FDFH5GT|Michael Jordan> is awesome', 'Michael Jordan is awesome'],
            ['A mention in the middle <@U2FDFH5GT|Michael Jordan> this is', 'A mention in the middle Michael Jordan this is'],
            ['A mention at the end <@U2FDFH5GT|Michael Jordan>', 'A mention at the end Michael Jordan'],
            ['Multiple mentions with the two best basketball players ever: <@U2FDFH5GT|Michael Jordan> and <@U2FDDDGT|Lebron James>.', 'Multiple mentions with the two best basketball players ever: Michael Jordan and Lebron James.'],
            ['This should do nothing <@U2FDFH5GT>', 'This should do nothing <@U2FDFH5GT>'],
        ];
    }

    /**
     * @dataProvider replaceLinksTagsWithUrlStringsProvider
     * @param $text
     * @param $expected
     */
    public function test_replaceLinksTagsWithUrlStrings($text, $expected) {
        $slackTextCleaner = new SlackTextCleaner($text);
        $this->assertEquals($expected, $slackTextCleaner->replaceLinksTagsWithUrlStrings()->getText());
    }

    public function replaceLinksTagsWithUrlStringsProvider() {
        return [
            ['I love <https://gofer.co|Gofer>', 'I love https://gofer.co'],
            ['<https://gofer.co> is awesome', 'https://gofer.co is awesome'],
            ['A mention in the middle <https://gofer.co/my-gofer/commands|Gofer> this is', 'A mention in the middle https://gofer.co/my-gofer/commands this is'],
            ['A mention at the end <http://www.gofer.co>', 'A mention at the end http://www.gofer.co'],
            ['Multiple mentions with <http://www.gofer.co> and <https://www.gofer.co|Gofer>.', 'Multiple mentions with http://www.gofer.co and https://www.gofer.co.'],
            ['This should do nothing <@U2FDFH5GT>', 'This should do nothing <@U2FDFH5GT>'],
        ];
    }



}