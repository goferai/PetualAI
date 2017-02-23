<?php

use Gofer\Highcharts\HighchartsOptions;
use Gofer\Highcharts\Options\Series;
use Gofer\Highcharts\Options\SeriesObject;
use Gofer\Highcharts\Options\xAxis;

class HighchartsImageTest extends PHPUnit_Framework_TestCase {
	
    public function test_getHighchartsImageUrl() {
        $series = new Series();
        $series->add((new SeriesObject())->setData([29.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]));
        $options = new HighchartsOptions();
        $options
            ->setXAxis((new xAxis())->setCategories(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun','Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']))
            ->setSeries($series);
        $imageUrl = (new \Gofer\Highcharts\HighchartsImage($options))
            ->buildImage()
            ->imageUrl();
        $this->assertGreaterThan(5, strlen($imageUrl));
        $this->assertStringEndsWith('.png', $imageUrl);
    }
    
}