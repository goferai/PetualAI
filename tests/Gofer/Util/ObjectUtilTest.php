<?php

use Gofer\ApiAi\Resources\ApiAiResponse;
use Gofer\ApiAi\Resources\ApiAiResponseMetadata;
use Gofer\SDK\Models\Entities\DatetimeEntity;
use Gofer\Util\ObjectUtil;

class ObjectUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider isEmptyProvider
     * @param $object
     * @param $expected
     */
    public function test_isEmpty($object, $expected) {
        $result = ObjectUtil::isEmpty($object);
        $this->assertEquals($expected, $result);
    }

    public function isEmptyProvider() {
        $object = new \stdClass();
        $object->property1 = true;
        $emptyObject = new \stdClass();
        $customObject = new ApiAiResponseMetadata();
        $customObject->initializeForJSON('{"intentId":"something"}');
        $customObjectEmpty = new ApiAiResponseMetadata();
        $customObjectNoEmptyFunction = new ApiAiResponse();
        return [
            [null, true],
            [false, true],
            [['array'], true],
            [new \stdClass(), true],
            [$object, false],
            [$emptyObject, true],
            [$customObject, false],
            [$customObjectEmpty, true],
            [$customObjectNoEmptyFunction, false],
        ];
    }

    /**
     * @dataProvider doesClassExistProvider
     * @param $className
     * @param $expected
     */
	public function test_doesClassExist($className, $expected) {
		$result = ObjectUtil::doesClassExist($className);
        $this->assertEquals($expected, $result);
	}

    public function doesClassExistProvider() {
        return [
            [DatetimeEntity::class, true],
            ['\\Gofer\\SDK\\Models\\Entities\\FakeClass', false],
        ];
    }

}