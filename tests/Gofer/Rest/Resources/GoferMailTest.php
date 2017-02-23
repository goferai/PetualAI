<?php

class GoferMailTest extends PHPUnit_Framework_TestCase {
	
	protected $preserveGlobalState = FALSE;
	protected $runTestInSeparateProcess = TRUE;
    private static $randomNumber;
	  
    public function testProcessInboundEmail() {
    	self::$randomNumber = rand(1,10000);
    	$postData = array (
    		'from'=> 'Chip Allen <chip@gofer.co>',
    		'to'=> 'chipallen22@yahoo.com, gofer2@devmail.gofer.co',
    		'subject' => 'Test GoferMail'.self::$randomNumber,
    		'html' => '<div dir="ltr">Test<div><br clear="all"><div><div class="gmail_signature"><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div><div dir="ltr"><div style="font-size:small;font-family:arial">Thanks,</div><div style="font-size:small;font-family:arial">Chip</div><div style="font-size:small;font-family:arial"><br></div><div style="font-size:small;font-family:arial">
    					___________________________<br>Chip Allen</div><div style="font-size:12.7272720336914px"><font face="arial" size="2">Founder at Gofer - An Artificial Assistant for Enterprise Apps</font><br><font face="arial" size="2">Email: <a href="mailto:chip@gofer.co" target="_blank">chip@gofer.co</a></font><div style="font-family:arial;font-size:small">Cell: 720.239.2447</div><div style="font-family:arial;font-size:small">Site: <a href="http://gofer.co" target="_blank">gofer.co</a><br>Schedule a Meeting With Me: <a href="http://vyte.in/chip" target="_blank">vyte.in/chip</a></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div>',
    		'text' => 'Test
    			
							Thanks,
							Chip
				
							___________________________
							Chip Allen
							Founder at Gofer - An Artificial Assistant for Enterprise Apps
							Email: chip@gofer.co
							Cell: 720.239.2447
							Site: gofer.co
							Schedule a Meeting With Me: vyte.in/chip
							',
    										'headers' => 'Received: by mx0040p1mdw1.sendgrid.net with SMTP id jxIinfPfwC Wed, 09 Mar 2016 21:45:36 +0000 (UTC)
								Received: from sender163-mail.zoho.com (sender163-mail.zoho.com [74.201.84.163]) by mx0040p1mdw1.sendgrid.net (Postfix) with ESMTPS id 99D73AC0846 for <gofer2@devmail.gofer.co>; Wed,  9 Mar 2016 21:45:36 +0000 (UTC)
								DomainKey-Signature: a=rsa-sha1; q=dns; c=nofws;  s=zoho; d=gofer.co;  h=mime-version:from:date:message-id:subject:to:content-type;  b=MDWToEfQCk3hJ11CHb25SlwU932uW7zzi8UI0QehOQFH9J2pLuItGS5gbGIGzmKQj3ayNvO1nuA0 ouFQVqO9Lh0Ml1q4PM6AoHu6sJ2zQZoR26cQdlf7iB3tcZcMPOCwpKaYj4FbTLBzPaCBiTqDJAuf GxfxEgjsICT9Wm+t7II=
								Received: from mail-qg0-f48.google.com (mail-qg0-f48.google.com [209.85.192.48]) by mx.zohomail.com with SMTPS id 1457559934830375.1938096039934; Wed, 9 Mar 2016 13:45:34 -0800 (PST)
								Received: by mail-qg0-f48.google.com with SMTP id y89so54212069qge.2 for <gofer2@devmail.gofer.co>; Wed, 09 Mar 2016 13:45:34 -0800 (PST)
								Received: by 10.140.109.136 with HTTP; Wed, 9 Mar 2016 13:45:14 -0800 (PST)
								X-Gm-Message-State: AD7BkJJRTMoXGG6MMv+P8eaNmbRiogVEzQllZKaTP7lfnpTLlgE5ZYwvjaBzBJkEekVUIm9p8H7hyDTuiDYNzg==
								X-Received: by 10.140.99.72 with SMTP id p66mr5495qge.16.1457559933871; Wed, 09 Mar 2016 13:45:33 -0800 (PST)
								MIME-Version: 1.0
								From: Chip Allen <chip@gofer.co>
								Date: Wed, 9 Mar 2016 13:45:14 -0800
								X-Gmail-Original-Message-ID: <CANxUiB4eyeM3D0osXf3K__Vmcrmt4rLDSiNowuTmKHymzH2xoA@mail.gmail.com>
								Message-ID: <CANxUiB4eyeM3D0osXf3K__Vmcrmt4rLDSiNowuTmKHymzH2xoA@mail.gmail.com>
								Subject: Test Subject 5
								To: chipallen22@yahoo.com, gofer2@devmail.gofer.co
								Content-Type: multipart/alternative; boundary=001a113cf9a218f260052da49ed4
								X-Zoho-Virus-Status: 1
								'
    	);
    	\Gofer\Util\WebServiceUtil::setPostFields($postData);
    	$this->expectOutputString('{"Result":true}');
    	\Gofer\Util\TestingUtil::mockRouterCall(null, 'post', '/api/goferMail');
    } 
    
    public static function tearDownAfterClass() {
    	//NOTE: tearDownAfterClass is getting called twice - not sure why - this ensures that it only runs once - cause later time the class restarts so this event id would be null
    	if (isset(self::$randomNumber)) {
    		\Gofer\Util\TestingUtil::executeSQL("delete from gofer.srvc_emails where email_subject = 'Test GoferMail".self::$randomNumber."'");
    	}
    }
    
}