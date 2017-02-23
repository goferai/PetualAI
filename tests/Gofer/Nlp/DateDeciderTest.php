<?php

//use Gofer\Nlp\NlpResponseBuilder;
//use Gofer\Wit\WitContext;
//use Gofer\Wit\WitStates;
//use Gofer\Wit\WitRequest;
//use Gofer\Nlp\NlpRequest;
//use Gofer\Util\WebServiceUtil;

class DateDeciderTest extends PHPUnit_Framework_TestCase {
	
	protected $contextDate = '2016-05-18T10:00:00-07:00';
	
	//Comment this oout and uncomment the below when you want to retest all the date formats.
	//Comment it out to remove it from the regular tests to speed things up and not ping WIT.AI over and over.
	
	public function test_Fake() {
		$this->assertEquals(1,1);
	}
	
// 	/**
// 	 * @dataProvider testsProvider
// 	 */
// 	public function testDecide_TestAllDates($text, $expectedDates, $expectedSpecificFlag) {
// 		$witResponse = $this->getWitResponse($text);
// 		$nlpResponse = $this->getNlpResponse($text);
// 		$dateDecider = new DateDecider($text);
// 		$now = new \DateTime($this->contextDate, new DateTimeZone('America/Los_Angeles'));
// 		$actualSpecificTimeFlag = false;
// 		$dateRanges = $dateDecider->decide($witResponse, $nlpResponse, 'America/Los_Angeles', $actualSpecificTimeFlag, $now);
// 		$this->assertCount(count($expectedDates), $dateRanges);
// 		$this->assertNotNull($dateRanges[0]->start);
// 		$this->assertEquals($expectedSpecificFlag, $actualSpecificTimeFlag);
// 		$index = 0;
// 		foreach ($expectedDates as $expected) {
// 			$this->assertEquals($expected[0], $dateRanges[$index]->start->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S));
// 			$this->assertEquals($expected[1], $dateRanges[$index]->end->format(DateUtil::FORMAT_DATE_YYYY_MM_DD_HH24_M_S));
// 			$index++;
// 		}
// 	}
	
// 	public function testsProvider() {
// 		return array(
// 				'schedule a meeting anytime thursday thru saturday' => array('schedule a meeting anytime thursday thru saturday', array(
// 						array('2016-05-19 00:00:00', '2016-05-22 00:00:00')
// 				), false)
// 				,
// 				'arrange a meeting for next week' => array('arrange a meeting for next week', array(
// 						array('2016-05-22 00:00:00', '2016-05-29 00:00:00')
// 				), false)
// 				,
// 				'arrange a meeting sometime monday thru wednesday' => array('arrange a meeting sometime monday thru wednesday', array(
// 						array('2016-05-23 00:00:00', '2016-05-26 00:00:00')
// 				), false)
// 				,
// 				'schedule a meeting for tomorrow' => array('schedule a meeting for tomorrow', array(
// 						array('2016-05-19 00:00:00', '2016-05-20 00:00:00')
// 				), false),
// 				'schedule a meeting for thursday or friday' => array('schedule a meeting for thursday or friday', array(
//     					array('2016-05-19 00:00:00', '2016-05-20 00:00:00'),
//     					array('2016-05-20 00:00:00', '2016-05-21 00:00:00')
//     			), false),
//     			'schedule a meeting for thursday at 1' => array('schedule a meeting for thursday at 1', array(
// 		    			array('2016-05-19 13:00:00', '2016-05-19 13:30:00')
// 		    	), true),
// 				'schedule a meeting for friday at 2' => array('schedule a meeting for friday at 2', array(
// 						array('2016-05-20 14:00:00', '2016-05-20 14:30:00')
// 				), true),
// 				'schedule a meeting for saturday at 3' => array('schedule a meeting for saturday at 3', array(
// 						array('2016-05-21 15:00:00', '2016-05-21 15:30:00')
// 				), true),
// 				'schedule a meeting for sunday at 4' => array('schedule a meeting for sunday at 4', array(
// 						array('2016-05-22 16:00:00', '2016-05-22 16:30:00')
// 				), true),
// 				'schedule a meeting for monday at 5' => array('schedule a meeting for monday at 5', array(
// 						array('2016-05-23 17:00:00', '2016-05-23 17:30:00')
// 				), true),
// 				'schedule a meeting for tuesday at 6' => array('schedule a meeting for tuesday at 6', array(
// 						array('2016-05-24 18:00:00', '2016-05-24 18:30:00')
// 				), true)
// 				,
// 				'schedule a meeting for wednesday at 7' => array('schedule a meeting for wednesday at 7', array(
// 						array('2016-05-25 07:00:00', '2016-05-25 07:30:00')
// 				), true)
// 				,
// 				'schedule a meeting for thursday at 8' => array('schedule a meeting for thursday at 8', array(
// 						array('2016-05-19 08:00:00', '2016-05-19 08:30:00')
// 				), true),
// 				'schedule a meeting for thursday at 9' => array('schedule a meeting for thursday at 9', array(
// 						array('2016-05-19 09:00:00', '2016-05-19 09:30:00')
// 				), true),
// 				'schedule a meeting for thursday at 10' => array('schedule a meeting for thursday at 10', array(
// 						array('2016-05-19 10:00:00', '2016-05-19 10:30:00')
// 				), true),
// 				'schedule a meeting for thursday at 11' => array('schedule a meeting for thursday at 11', array(
// 						array('2016-05-19 11:00:00', '2016-05-19 11:30:00')
// 				), true),
// 				'schedule a meeting for thursday at noon' => array('schedule a meeting for thursday at noon', array(
// 						array('2016-05-19 12:00:00', '2016-05-19 12:30:00')
// 				), true),
// 				'schedule a meeting for thursday at 12' => array('schedule a meeting for thursday at 12', array(
// 						array('2016-05-19 12:00:00', '2016-05-19 12:30:00')
// 				), true),
// 				'schedule a meeting for thursday at 1pm' => array('schedule a meeting for thursday at 1pm', array(
// 						array('2016-05-19 13:00:00', '2016-05-19 13:30:00')
// 				), true),
//     			'schedule a meeting for thursday at 1:30' => array('schedule a meeting for thursday at 1:30', array(
// 		    			array('2016-05-19 13:30:00', '2016-05-19 14:00:00')
//     			), true),
// 				'schedule a meeting for thursday at 2:15' => array('schedule a meeting for thursday at 2:15', array(
// 						array('2016-05-19 14:15:00', '2016-05-19 14:45:00')
// 				), true),
// 				'schedule a meeting for thursday at 3:45' => array('schedule a meeting for thursday at 3:45', array(
// 						array('2016-05-19 15:45:00', '2016-05-19 16:15:00')
// 				), true),
// 				'schedule a meeting for thursday at 4:30' => array('schedule a meeting for thursday at 4:30', array(
// 						array('2016-05-19 16:30:00', '2016-05-19 17:00:00')
// 				), true),
// 				'schedule a meeting for thursday at 4:30' => array('schedule a meeting for thursday at 4:30', array(
// 						array('2016-05-19 16:30:00', '2016-05-19 17:00:00')
// 				), true),
// 				"I'm copying my Gofer assistant to arrange a time for this afternoon" => array("I'm copying my Gofer assistant to arrange a time for this afternoon", array(
// 						array('2016-05-18 12:00:00', '2016-05-18 19:00:00')
// 				), false)
				
// 		);
// 	}
    
//    protected function getWitResponse($text) {
//    	$witContext = new WitContext();
//    	$witContext->setStates(WitStates::STATE_EMAIL);
//    	$witContext->setReferenceTime($this->contextDate);
//    	$witRequest = new WitRequest();
//    	$witResponse = $witRequest	->setText($text)
//    	->setContext($witContext)
//    	->query();
//    	return $witResponse;
//    }
//
//    protected function getNlpResponse($text) {
//    	$nlpRequest = new NlpRequest($text, $this->contextDate, 30);
//    	$responseJson = WebServiceUtil::callService(GOFER_JAVA_ML_BASE_URL.'/nlp/tagDates', 'POST', $nlpRequest->toJSON());
//        $nlpResponseBuilder = new NlpResponseBuilder();
//        return $nlpResponseBuilder->buildForJSON($responseJson);
//    }
    
}