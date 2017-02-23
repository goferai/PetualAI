<?php

use Gofer\Util\StringUtil;

class StringUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider wordsToRemove
     * @param $subject
     * @param $words
     * @param $expected
     */
	public function test_removeWordFromEnd($subject, $words, $expected) {
		$result = StringUtil::removeWordFromEnd($words, $subject);
        $this->assertEquals($expected, $result);
	}

    public function wordsToRemove() {
        return [
            ['Remove this word$', 'word$', 'Remove this'],
            ['Do not remove these words', 'word', 'Do not remove these words'],
            ['Remove first found word1 word2', ['word1', 'word2'], 'Remove first found word1'],
        ];
    }

    /**
     * @dataProvider fullNames
     * @param $fullName
     * @param $expected
     */
    public function test_extractFirstLastNameFromFull($fullName, $expected) {
		$result = StringUtil::extractFirstLastNameFromFull($fullName);
        $this->assertEquals($expected, $result);
	}
	
	public function fullNames() {
		return array(
			array('Tom John Smith', array('Tom John', 'Smith')),
			array('Tom  Smith', array('Tom', 'Smith')),
            array(' Tom  Smith', array('Tom', 'Smith')),
			array('Tom', array('Tom', '')),
		);
	}

    /**
     * @dataProvider replacements
     * @param $word
     * @param $replacement
     * @param $expected
     */
    public function test_replaceUnderscores($word, $replacement, $expected) {
		$result = StringUtil::replaceUnderscores($word, $replacement);
        $this->assertEquals($expected, $result);
    }
	
	public function replacements() {
		return array(
			array('multi_part_word', ' ', 'multi part word'),
			array('multi_part_word', '', 'multipartword'),
			array('multi_part_word', ' - ', 'multi - part - word'),
		);
	}

    /**
     * @dataProvider lowerCaseTexts
     * @param $word
     * @param $includeUnderscore
     * @param $expected
     */
    public function test_convertToTitleCase($word, $includeUnderscore, $expected) 
	{
		$result = StringUtil::convertToTitleCase($word, $includeUnderscore);
        $this->assertEquals($expected, $result);
    }
	
	public function lowerCaseTexts() {
		return array(
			array('multi word text', FALSE, 'Multi Word Text'),
			array('multi  word text', FALSE, 'Multi  Word Text'),
			array('multi_word_text', FALSE, 'Multi_word_text'),
			array('multi_word_text', TRUE, 'Multi_Word_Text'),
		);
	}

    /**
     * @dataProvider toCamelCaseTexts
     * @param $word
     * @param $expected
     */
    public function test_convertToCamelCase($word, $expected)
	{
		$result = StringUtil::convertToCamelCase($word);
        $this->assertEquals($expected, $result);

    }
	
	public function toCamelCaseTexts() {
		return array(
			array('multi word text', 'multiWordText'),
			array('multi_word_text', 'multiWordText'),
		);
	}

    /**
     * @dataProvider toUnderscoreTexts
     * @param $word
     * @param $expected
     */
	public function test_convertToUnderscoreCase($word, $expected) {
		$result = StringUtil::convertToUnderscoreCase($word);
        $this->assertEquals($expected, $result);
	}
	
	public function toUnderscoreTexts() {
		return array(
			array('multi Word teXt', 'multi_word_text'),
			array('Multi Word  texT', 'multi_word__text'),
		);
	}

    /**
     * @dataProvider titleToUnderscore
     * @param $word
     * @param $expected
     */
    public function test_convertTitleToUnderscoreCase($word, $expected) {
		$result = StringUtil::convertTitleToUnderscoreCase($word);
        $this->assertEquals($expected, $result);
	}
	
	public function titleToUnderscore() {
		return array(
			array('MultiWordText', 'multi_word_text'),
			array('multiWordText', 'multi_word_text'),
		);
	}

    /**
     * @dataProvider amounts
     * @param $amount
     * @param $expected
     */
	public function test_convertAmountToInteger($amount, $expected) {
		$result = StringUtil::convertAmountToInteger($amount);
        $this->assertEquals($expected, $result);
	}
	
	public function amounts() {
		return array(
			array('$10,100,100', '10100100'),
			array('$10,100,100.68', '10100100'),
		);
	}

    /**
     * @dataProvider urlsIntoHtmlLinks
     * @param $string
     * @param $expected
     */
	public function test_turnUrlsIntoHtmlLinks($string, $expected) {
		$result = StringUtil::turnUrlsIntoHtmlLinks($string);
        $this->assertEquals($expected, $result);
	}
	
	public function urlsIntoHtmlLinks() {
		return array(
			array('https://gofer.co', 
				'<a href="https://gofer.co" target="_blank" title="https://gofer.co">https://gofer.co</a>'),
			array('http://gofer.co/?param=val', 
				'<a href="http://gofer.co/?param=val" target="_blank" title="http://gofer.co/?param=val">http://gofer.co/?param=val</a>'),
		);
	}

    /**
     * @dataProvider htmlLinksIntoUrlStrings
     * @param $string
     * @param $expected
     */
    public function test_turnHtmlLinksIntoUrlStrings($string, $expected) {
		$result = StringUtil::turnHtmlLinksIntoUrlStrings($string);
        $this->assertEquals($expected, $result);
    }
	
	public function htmlLinksIntoUrlStrings() {
		return array(
			array('<a href="https://gofer.co" target="_blank" title="https://gofer.co">https://gofer.co</a>', 'https://gofer.co'),
			array('<a href="http://gofer.co/?param=val" target="_blank" title="http://gofer.co">http://gofer.co/?param=val</a>', 'http://gofer.co/?param=val'),
			
			// No Url return
			array('<a href="http://gofer.co" target="_blank" title="http://gofer.co">gofer.co only</a>', 'gofer.co only'),
		);
	}
	
    public function test_convertEmailTextToHTML() {
		$text = "
				First paragraph
				Second paragraph
				
				Third paragraph
				Forth paragraph
				";
		$expected = "
				<div></div>
				<div>First paragraph</div>
				<div>Second paragraph</div>
				<div></div><div>Third paragraph</div>
				<div>Forth paragraph</div>
				<div></div>";
		
		$result = StringUtil::convertEmailTextToHTML($text);
		
        $this->assertEquals(
				preg_replace('/\s/', '', $expected), 
				preg_replace('/\s/', '', $result)
		);
		
		// The tested function fails to detect the real double new lines
		// It must delete whitespace before it adds <br>
	}

    public function test_stripDoubleSpaces() {
		$string = 'Three  words  string';
		$expected = 'Three words string';
		$result = StringUtil::stripDoubleSpaces($string);
		$this->assertEquals($expected, $result);
    }
	
     public function testHtmlDecode1() {
    	$text = '<div dir="ltr">John,<div><br></div><div>I&#39;d love to meet. I&#39;m copying my assistant Gofer who will arrange.';
    	$result = StringUtil::html_character_reference_decode($text);
    	$expected = "<div dir=\"ltr\">John,<div><br></div><div>I'd love to meet. I'm copying my assistant Gofer who will arrange.";
        $this->assertEquals($expected, $result);
    }

   public function test_extractTextFromHTML() {
		$html = "<div dir=\"ltr\">John,<div><br></div><div>I'd love to meet. I'm copying my assistant Gofer who will arrange.";
		$expected = "John,I'd love to meet. I'm copying my assistant Gofer who will arrange.";
		$result = StringUtil::extractTextFromHTML($html);
		$this->assertEquals($expected, $result);
	}
	
	public function test_strpos_all() {
		$needle = 'nee';
		$haystack = 'A needle in a haystack. A needle in a haystack. A needle in a haystack.';
		$expected = array('2', '26', '50');
		$result = StringUtil::strpos_all($needle, $haystack);
		$this->assertEquals($expected, $result);
	}
	
	public function test_pluralToSingular_Normal() {
        $word = 'Users';
        $singular = StringUtil::pluralToSingular($word);
        $this->assertEquals('User', $singular);
    }

    public function test_pluralToSingular_IgnoreAlways() {
        $word = 'UserBusySlotAlways';
        $singular = StringUtil::pluralToSingular($word);
        $this->assertEquals($word, $singular);
    }

    /**
     * @dataProvider providerIsPluralWord
     * @param $word
     * @param $expected
     */
    public function test_isPluralWord($word, $expected) {
        $result = StringUtil::isPluralWord($word);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider providerIsPluralWord
     * @param $word
     * @param $expected
     */
    public function test_isSingularWord($word, $expected) {
        $result = StringUtil::isSingularWord($word);
        $this->assertNotEquals($expected, $result);
    }

    public function providerIsPluralWord() {
        return [
            ['comment', false],
            ['comments', true],
        ];
    }

    /**
     * @dataProvider lastStrings
     * @param $word
     * @param $numberOfCharacters
     * @param $expected
     */
    public function test_last($word, $numberOfCharacters, $expected) {
 		$result = StringUtil::last($word, $numberOfCharacters);
        $this->assertEquals($expected, $result);
   }

	public function lastStrings() {
		return array(
			array('multi word string', '1', 'g'),
			array('multi word string.', '1', '.'),
			array('str', '5', 'str'),
		);
	}

    /**
     * @dataProvider firstStrings
     * @param $word
     * @param $numberOfCharacters
     * @param $expected
     */
    public function test_first($word, $numberOfCharacters, $expected) {
        $result = StringUtil::first($word, $numberOfCharacters);
        $this->assertEquals($expected, $result);
    }

    public function firstStrings() {
        return array(
            array('multi word string', '1', 'm'),
            array('multi word string.', '6', 'multi '),
            array('str', '5', 'str'),
            array('test length longer', '1600', 'test length longer'),
        );
    }

    /**
     * @dataProvider afterLastProvider
     * @param $subject
     * @param $search
     * @param $expected
     */
    public function test_afterLast($subject, $search, $expected) {
        $result = StringUtil::afterLast($subject, $search);
        $this->assertEquals($expected, $result);
    }

    public function afterLastProvider() {
        return array(
            array('multi word string', ' ', 'string'),
            array('asldfajsd.png', '.', 'png'),
            array('', '5', ''),
            array('a-', '-', 'a-'),
        );
    }

    /**
     * @dataProvider startsWithProvider
     * @param $subject
     * @param $search
     * @param $expected
     */
    public function test_startsWith($subject, $search, $expected) {
        $result = StringUtil::startsWith($subject, $search);
        $this->assertEquals($expected, $result);
    }

    public function startsWithProvider() {
        return array(
            array('multi word string', 'multi', true),
            array('asldfajsd.png', 'search is too long ', false),
            array('', '', false),
            array('a-', '-', false),
            array('a', 'a', true),
        );
    }

    /**
     * @dataProvider endsWithProvider
     * @param $subject
     * @param $search
     * @param $expected
     */
    public function test_endsWith($subject, $search, $expected) {
        $result = StringUtil::endsWith($subject, $search);
        $this->assertEquals($expected, $result);
    }

    public function endsWithProvider() {
        return array(
            array('multi word string', 'string', true),
            array('asldfajsd.png', 'search is too long asldfajsd.png', false),
            array('', '', false),
            array('a-', 'a', false),
            array('a', 'a', true),
        );
    }


    /**
     * @dataProvider matchesPatternProvider
     * @param $subject
     * @param $patterns
     * @param $expected
     */
    public function test_matchesPattern($subject, $patterns, $expected) {
        $result = StringUtil::matchesPattern($subject, $patterns);
        $this->assertEquals($expected, $result);
    }

    public function matchesPatternProvider() {
        return [
            ['this is a match', '/match/', true],
            ['this is not a match', '/no match/', false],
            ['this has multiple patterns', ['/no match/', '/.*has/'], true],
            ['this has multiple patterns and none match', ['/no match/', '/has none/'], false],
        ];
    }

    /**
     * @dataProvider getMatchedPatternProvider
     * @param $subject
     * @param $patterns
     * @param $expected
     */
    public function test_getMatchedPattern($subject, $patterns, $expected) {
        $result = StringUtil::getMatchedPattern($subject, $patterns);
        $this->assertEquals($expected, $result);
    }

    public function getMatchedPatternProvider() {
        return [
            ['open the acme supply line account', '/^open (?:the )?([a-zA-Z0-9\s-_]{2,}) account$/', 'acme supply line'],
            ['this is not a match', '/(no match)/', false],
            ['this has multiple patterns', ['/no match/', '/.*has ([\w\s]+)/'], 'multiple patterns'],
            ['this has multiple patterns and none match', ['/no match/', '/has none/'], false],
        ];
    }

    /**
     * @dataProvider getIntegersProvider
     * @param $subject
     * @param $expected
     */
    public function test_getIntegers($subject, $expected) {
        $result = StringUtil::getIntegers($subject);
        $this->assertEquals($expected, $result);
    }

    public function getIntegersProvider() {
        return [
            ['123', '123'],
            ['a123a', '123'],
            ['a-.+a', ''],
            ['-100', '100'],
        ];
    }

    /**
     * @dataProvider convertNewLinesToUnixNewLinesProvider
     * @param $subject
     * @param $expected
     */
    public function test_convertNewLinesToUnixNewLines($subject, $expected) {
        $result = StringUtil::convertNewLinesToUnixNewLines($subject);
        $this->assertEquals($expected, $result);
    }

    public function convertNewLinesToUnixNewLinesProvider() {
        return [
            ['123', '123'],
            ["123\n", '123'.PHP_EOL],
            ["123\r\n", '123'.PHP_EOL],
            ["123\r", '123'.PHP_EOL],
            ["123\r\n\r\n", '123'.PHP_EOL.PHP_EOL],
            ['literal letters should not convert \r\n\r\n', 'literal letters should not convert \r\n\r\n'],
        ];
    }

    /**
     * @dataProvider hasSpecialCharactersProvider
     * @param $subject
     * @param $expected
     */
    public function test_hasSpecialCharacters($subject, $expected) {
        $result = StringUtil::hasSpecialCharacters($subject);
        $this->assertEquals($expected, $result);
    }

    public function hasSpecialCharactersProvider() {
        return [
            ['123', false],
            ['""', true],
            ['<b>', true],
            ["''", true],
            ["&", true],
            ["", false],
        ];
    }

    /**
     * @dataProvider escapeProvider
     * @param $subject
     * @param $expected
     */
    public function test_escape($subject, $expected) {
        $result = StringUtil::escape($subject);
        $this->assertEquals($expected, $result);
    }

    public function escapeProvider() {
        return [
            ['123', '123'],
            ['<b>', '&lt;b&gt;'],
            ['a_b', 'a_b'],
            ['a & b', 'a &amp; b'],
        ];
    }

    /**
     * @dataProvider sanitizeProvider
     * @param $subject
     * @param $expected
     */
    public function test_sanitize($subject, $expected) {
        $result = StringUtil::sanitize($subject);
        $this->assertEquals($expected, $result);
    }

    public function sanitizeProvider() {
        return [
            ['123', '123'],
            ['<b>', ''],
            ['<b>strong</b>', 'strong'],
            ['a_b', 'a_b'],
            ['a & b', 'a & b'],
        ];
    }

}