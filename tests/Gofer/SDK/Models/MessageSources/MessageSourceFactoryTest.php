<?php

use Gofer\SDK\Models\MessageSources\GoferWindowsSource;
use Gofer\SDK\Models\MessageSources\MessageSourceFactory;

class MessageSourceFactoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider buildForSourceIdProvider
     * @param $sourceId
     * @param $expectedClass
     */
	public function test_buildForSourceId($sourceId, $expectedClass) {
        $factory = new MessageSourceFactory();
        $source = $factory->buildForSourceId($sourceId);
		$this->assertInstanceOf($expectedClass, $source);
	}

    public function buildForSourceIdProvider() {
        return [
            [1, GoferWindowsSource::class],
            ["1", GoferWindowsSource::class],
        ];
    }


}