<?php

use Gofer\Util\EmailUtil;
use Gofer\Util\StringUtil;

class EmailUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider splitMultipleEmailsProvider
     * @param $emailString
     * @param $expected
     */
	public function test_splitMultipleEmails($emailString, $expected) {
        $this->assertEquals($expected, EmailUtil::splitMultipleEmails($emailString));
	}

    public function splitMultipleEmailsProvider() {
        return [
            ['e@x.com', ['e@x.com']], //single

            //multiple with comma or semi colon or spaces
            ['e@x.com;e2@x.com', ['e@x.com', 'e2@x.com']],
            ['e@x.com,e2@x.com', ['e@x.com', 'e2@x.com']],
            [' e@x.com,e2@x.com', ['e@x.com', 'e2@x.com']],
            [' e@x.com , e2@x.com ', ['e@x.com', 'e2@x.com']],
            [' e@x.com , e2@x.com ', ['e@x.com', 'e2@x.com']],

            //name formatted email
            ['"Owens, Tani" <towens@qualcom.com>', ['"Owens, Tani" <towens@qualcom.com>']],
            ['"Owens, Tani" <towens@qualcom.com>;"Owens2, Tani" <towens2@qualcom.com>', ['"Owens, Tani" <towens@qualcom.com>','"Owens2, Tani" <towens2@qualcom.com>']],
            ['"Owens, Tani" <towens@qualcom.com>,"Owens2, Tani" <towens2@qualcom.com>', ['"Owens, Tani" <towens@qualcom.com>','"Owens2, Tani" <towens2@qualcom.com>']],

            //weird address
            ['', ['']],
            [',', ['','']],
            ['e@x.com.com', ['e@x.com.com']],
            ['"tani ,,;;; owens" <e@x.com.com>', ['"tani ,,;;; owens" <e@x.com.com>']],

            //examples from site
            ['"Gofer" <gofer1@mail.gofer.co>', ['"Gofer" <gofer1@mail.gofer.co>']],
            ['Gofer <auto@gofer.co>', ['Gofer <auto@gofer.co>']],
            ['"Gofer" <gofer1@mail.gofer.co>,Gofer <auto@gofer.co>', ['"Gofer" <gofer1@mail.gofer.co>','Gofer <auto@gofer.co>']],
            [' "Gofer"  <gofer1@mail.gofer.co>  ; Gofer <auto@gofer.co> ', ['"Gofer" <gofer1@mail.gofer.co>','Gofer <auto@gofer.co>']],

            [
                'Tink Tank <tinktankstudio@gmail.com>, "S Stanly" <suneil.stanly@gmail.com>, "Arzish Azam" <malikarzish@gmail.com>, "Sun Stubs" <caleb@sunstubs.com>',
                [
                    'Tink Tank <tinktankstudio@gmail.com>',
                    '"S Stanly" <suneil.stanly@gmail.com>',
                    '"Arzish Azam" <malikarzish@gmail.com>',
                    '"Sun Stubs" <caleb@sunstubs.com>'
                ]
            ]
        ];
    }


    /**
     * @dataProvider isValidEmailStringProvider
     * @param $emailString
     * @param $expected
     */
    public function test_isValidEmailString($emailString, $expected) {
        $this->assertEquals($expected, EmailUtil::isValidEmailString($emailString));
    }

    public function isValidEmailStringProvider() {
        return [
            ['e@x.com', true],
            ['"Owens, Tani" <towens@qualcom.com>', true],
            ['Tani <towens>', false],
            ['', false],
        ];
    }

}