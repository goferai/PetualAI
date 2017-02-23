<?php

use Gofer\Messages\IntentsAdapter\ApiAiEntityAdapters\ApiAiEntityAdapterFactory;
use Gofer\Messages\IntentsAdapter\ApiAiEntityAdapters\DateTimeAdapter;
use Gofer\SDK\Models\Entities\DatetimeEntity;
use Gofer\SDK\Services\MessageEntity;
use Gofer\Util\DateUtil;
use Gofer\Util\TestingUtil;

class DateTimeAdapterTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider convertToMessageEntity_Provider
     * @param $entityValue
     * @param $expectedValue
     * @param $expectedGrain
     * @param null $expectedFrom
     * @param null $expectedTo
     */
    public function test_convertToMessageEntity($entityValue, $expectedValue, $expectedGrain, $expectedFrom = null, $expectedTo = null) {
        $dateTimeAdapter = ApiAiEntityAdapterFactory::build(DatetimeEntity::ENTITY_NAME, json_decode($entityValue), 'Unecessary', DateUtil::TZ_PST);
        $messageEntity = $dateTimeAdapter->convertToMessageEntity();
        $this->assertEquals($expectedValue, $messageEntity->getValue());
        $this->assertEquals($expectedGrain, $messageEntity->getGrain());
        $this->assertEquals($expectedFrom, $messageEntity->getFrom());
        $this->assertEquals($expectedTo, $messageEntity->getTo());
    }

    public function convertToMessageEntity_Provider() {
        return [
            ['{"date-period":"2017-01-01/2017-01-07"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_WEEK],
            ['{"date-period":"2017-01-01/2017-01-31"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_MONTH],
            ['{"date-period":"2017-01-01/2017-03-31"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_QUARTER],
            ['{"date-period":"2017-01-01/2017-12-31"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_YEAR],
            ['{"date-period":"2017-01-01/2017-01-01"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_DAY],
            ['{"date-period":"2017-01-01/2017-01-02"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_DAY],
            ['{"date":"2017-01-01"}', (new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_DAY],
            ['{"date-time":"2017-01-03T11:00:00Z"}', (new DateTime('2017-01-03T11:00:00', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601), MessageEntity::GRAIN_HOUR],
            [
                '{"date-time":"2017-01-05T12:00:00Z/2017-01-05T16:00:00Z"}',
                '2017-01-05T12:00:00-08:00-2017-01-05T16:00:00-08:00-hour',
                MessageEntity::GRAIN_HOUR,
                (new DateTime('2017-01-05T12:00:00', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601),
                (new DateTime('2017-01-05T16:00:00', new DateTimeZone(DateUtil::TZ_PST)))->format(DateUtil::FORMAT_ISO8601)
            ],
        ];
    }

    /**
     * @dataProvider getStartForDatePeriod_Provider
     * @param $datePeriodString
     * @param $expected
     */
    public function test_getStartForDatePeriod($datePeriodString, $expected) {
        $method = TestingUtil::getClassPrivateMethod(DateTimeAdapter::class, 'getStartForInterval');
        $dateTimeAdapter = ApiAiEntityAdapterFactory::build(DatetimeEntity::ENTITY_NAME, 'Unecessary', 'Unecessary', DateUtil::TZ_PST);
        $result = $method->invokeArgs($dateTimeAdapter, [$datePeriodString]);
        $this->assertEquals($expected, $result);
    }

    public function getStartForDatePeriod_Provider() {
        return [
            ['2017-01-01/2017-01-07', new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST))],
            ['2017-01-01/2017-03-31', new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST))],
            ['2017-01-01/2017-12-31', new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST))],
            ['2017-01-01/2017-01-01', new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST))],
        ];
    }

    /**
     * @dataProvider getEndForDatePeriod_Provider
     * @param $datePeriodString
     * @param $expected
     */
    public function test_getEndForDatePeriod($datePeriodString, $expected) {
        $method = TestingUtil::getClassPrivateMethod(DateTimeAdapter::class, 'getEndForInterval');
        $dateTimeAdapter = ApiAiEntityAdapterFactory::build(DatetimeEntity::ENTITY_NAME, 'Unecessary', 'Unecessary', DateUtil::TZ_PST);
        $result = $method->invokeArgs($dateTimeAdapter, [$datePeriodString]);
        $this->assertEquals($expected, $result);
    }

    public function getEndForDatePeriod_Provider() {
        return [
            ['2017-01-01/2017-01-07', new DateTime('2017-01-07', new DateTimeZone(DateUtil::TZ_PST))],
            ['2017-01-01/2017-03-31', new DateTime('2017-03-31', new DateTimeZone(DateUtil::TZ_PST))],
            ['2017-01-01/2017-12-31', new DateTime('2017-12-31', new DateTimeZone(DateUtil::TZ_PST))],
            ['2017-01-01/2017-01-01', new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST))],
        ];
    }

    /**
     * @dataProvider getGrainForDatePeriod_Provider
     * @param $start
     * @param $end
     * @param $expected
     */
	public function test_getGrainForDatePeriod($start, $end, $expected) {
        $method = TestingUtil::getClassPrivateMethod(DateTimeAdapter::class, 'getGrainForDatePeriod');
        $dateTimeAdapter = ApiAiEntityAdapterFactory::build(DatetimeEntity::ENTITY_NAME, 'Unecessary', 'Unecessary', DateUtil::TZ_PST);
        $result = $method->invokeArgs($dateTimeAdapter, [$start, $end]);
        $this->assertEquals($expected, $result);
	}

    public function getGrainForDatePeriod_Provider() {
        return [
            [new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)), new DateTime('2017-01-07', new DateTimeZone(DateUtil::TZ_PST)), MessageEntity::GRAIN_WEEK],
            [new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)), new DateTime('2017-01-31', new DateTimeZone(DateUtil::TZ_PST)), MessageEntity::GRAIN_MONTH],
            [new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)), new DateTime('2017-03-31', new DateTimeZone(DateUtil::TZ_PST)), MessageEntity::GRAIN_QUARTER],
            [new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)), new DateTime('2017-12-31', new DateTimeZone(DateUtil::TZ_PST)), MessageEntity::GRAIN_YEAR],
            [new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)), new DateTime('2017-01-01', new DateTimeZone(DateUtil::TZ_PST)), MessageEntity::GRAIN_DAY],
        ];
    }
	
}