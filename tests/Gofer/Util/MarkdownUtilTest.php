<?php

use Gofer\Util\MarkdownUtil;

class MarkdownUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider stripMarkdownProvider
     * @param $text
     * @param $expected
     */
    public function test_stripMarkdown($text, $expected) {
        $result = MarkdownUtil::stripMarkdown($text);
        $this->assertEquals($expected, $result);
    }

    public function stripMarkdownProvider() {
        return [
            ['**bold**', 'bold'],
            ['*bold**', '*bold**'],
            ['*italic*', 'italic'],
        ];
    }

    /**
     * @dataProvider replaceMarkdownTagProvider
     * @param $search
     * @param $replace
     * @param $text
     * @param $expected
     */
    public function test_replaceMarkdownTag($search, $replace, $text, $expected) {
        $result = MarkdownUtil::replaceMarkdownTag($search, $replace, $text);
        $this->assertEquals($expected, $result);
    }

    public function replaceMarkdownTagProvider() {
        return [
            ['**', '*', 'No change', 'No change'],
            ['**', '*', '**bold**', '*bold*'],
            ['**', '*', '**bold** **text**', '*bold* *text*'],
            ['*', '_', '*italic*', '_italic_'],
            [['*','**'], ['_','*'], '**bold** and *italic*', '*bold* and _italic_'],
            [['*','**'], ['_','*'], '**bold** **bold** and *italic* *italic*', '*bold* *bold* and _italic_ _italic_'],
        ];
    }

    /**
     * @dataProvider convertMarkdownToHtmlProvider
     * @param $text
     * @param $convertLineBreaks
     * @param $expected
     */
    public function test_convertMarkdownToHtml($text, $convertLineBreaks, $expected) {
        $result = MarkdownUtil::convertMarkdownToHtml($text, $convertLineBreaks);
        $this->assertEquals($expected, $result);
    }

    public function convertMarkdownToHtmlProvider() {
        return [
            ['No change'.PHP_EOL.'To the text', false, 'No change'.PHP_EOL.'To the text'],
            ['Change **Bold** and *Italic*'.PHP_EOL.'text', true, 'Change <strong>Bold</strong> and <em>Italic</em><br />text'],
            ['Change'.PHP_EOL.'To the text', true, 'Change<br />To the text'],
            ['#Change'.PHP_EOL.'header text and '.PHP_EOL.'##sub header text', true, '<h1>Change</h1>header text and <h2>sub header text</h2>'],
            ['Do not change list elements'.PHP_EOL.'1. Item 1'.PHP_EOL.'2. Item 2', true, 'Do not change list elements<br />1. Item 1<br />2. Item 2'],
            ['Change inline `code` elements', true, 'Change inline <code>code</code> elements'],
            ['Change inline `code` elements and'.PHP_EOL.'```'.PHP_EOL.'block code too'.PHP_EOL.'```', true, 'Change inline <code>code</code> elements and<pre><code>block code too</code></pre>'],
            [
                '1. Request New Meeting: "Setup a 30-minute skype call with George Washington on next week"'.PHP_EOL.'2. Find Accounts Nearby: "Show accounts within 1000 yards of me"'.PHP_EOL.'3. Reporting Request: "Graph sales by salesperson and quarter"'.PHP_EOL.'4. Open an Opportunity: "Open the Microsoft"'.PHP_EOL.''.PHP_EOL.'View the full list of commands at http://localhost:9002/my-gofer/commands',
                true,
                '1. Request New Meeting: &quot;Setup a 30-minute skype call with George Washington on next week&quot;<br />2. Find Accounts Nearby: &quot;Show accounts within 1000 yards of me&quot;<br />3. Reporting Request: &quot;Graph sales by salesperson and quarter&quot;<br />4. Open an Opportunity: &quot;Open the Microsoft&quot;<div><br /><div>View the full list of commands at <a href="http://localhost:9002/my-gofer/commands">http://localhost:9002/my-gofer/commands</a>'
            ],
        ];
    }
}
