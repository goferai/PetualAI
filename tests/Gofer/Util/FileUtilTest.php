<?php

class FileUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider getMimeTypeProvider
     * @param $filename
     * @param $expected
     */
	public function test_getMimeType($filename, $expected) {
		$result = \Gofer\Util\FileUtil::getMimeType($filename);
        $this->assertEquals($expected, $result);
	}

    public function getMimeTypeProvider() {
        return [
            ['23sldfjlk.png', 'image/png'],
        ];
    }

}