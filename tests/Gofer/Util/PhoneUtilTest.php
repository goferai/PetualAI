<?php

class PhoneUtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider convert10DigitPhoneToE164Provider
     * @param $phone
     * @param $countryCode
     * @param $expected
     */
    public function test_convert10DigitPhoneToE164($phone, $countryCode, $expected) {
        $result = \Gofer\Util\PhoneUtil::convert10DigitPhoneToE164($phone, $countryCode);
        $this->assertEquals($expected, $result);
    }

    public function convert10DigitPhoneToE164Provider() {
        return [
            ['(720) 239-2447', '1', '+17202392447'],
        ];
    }

}
