<?php

use Gofer\SDK\Models\Entities\EntityFactory;
use Gofer\SDK\Models\Entities\TrainingNameEntity;
use Gofer\SDK\Models\Entities\UnknownEntity;

class EntityFactoryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider buildForEntityNameProvider
     * @param $name
     * @param $expectedClass
     */
    public function test_buildForEntityName($name, $expectedClass) {
        $this->assertInstanceOf($expectedClass, EntityFactory::buildForEntityName($name));
    }

    public function buildForEntityNameProvider() {
        return [
            [TrainingNameEntity::ENTITY_NAME, TrainingNameEntity::class],
            ['Not Found', UnknownEntity::class],
        ];
    }
	
}