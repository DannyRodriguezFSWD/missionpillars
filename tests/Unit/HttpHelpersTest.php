<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HttpHelpersTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testDisplayDateTimeRange()
    {
	// times are in UTC
        $start = '2020-12-09 12:00';
        $end1 = '2020-12-09 14:00';
        $end2 = '2020-12-10 5:00';
	$timezone = 'America/New_York';


        $result = displayDateTimeRange($start, $end1, true, $timezone);
        $this->assertEquals('December 9th, 2020',$result);

        $result = displayDateTimeRange($start, $end1, false, $timezone);
        $this->assertEquals( 'December 9th, 2020 7:00 am - 9:00 am',$result);

        $result = displayDateTimeRange($start, $end2, false, $timezone);
        $this->assertEquals( 'December 9th, 2020 7:00 am - December 10th, 2020 12:00 am',$result);
    }
    
}
