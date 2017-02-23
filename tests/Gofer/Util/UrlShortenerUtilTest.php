<?php

use Gofer\Util\UrlShortenerUtil;

class UrlShortenerUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider urlsToShortenProvider
     * @param $url
     * @param $expectedDifferentUrl
     */
	public function test_shorten($url, $expectedDifferentUrl) {
		$result = UrlShortenerUtil::shorten($url);
        $actuallyDifferentUrl = ($url !== $result);
        $this->assertEquals($expectedDifferentUrl, $actuallyDifferentUrl);
	}

    public function urlsToShortenProvider() {
        return [
            ['https://gofer.co',  false],
            ['https://server.gofer.co/api/test/test',  true],
            ['h',  false],
        ];
    }

}