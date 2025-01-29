<?php

namespace Tests\Unit;

use App\Traits\ModuleTrait;
use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TestClass {
    use ModuleTrait;
}

class ModuleTraitTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetNumberOfDaysToBill()
    {
        $test = new TestClass();
        $today = \Carbon\Carbon::now()->endOfDay();
        $fourdaysago = \Carbon\Carbon::now()->startOfDay()->subDays(4);
        $fifteendaysago = \Carbon\Carbon::now()->startOfDay()->subDays(15);
        $thirtydaysago = \Carbon\Carbon::now()->startOfDay()->subDays(30);
        $fortyfivedaysago = \Carbon\Carbon::now()->startOfDay()->subDays(45);

	// Normal active modules bill to the end of the month based on the the from date argument
        $days = $test->getNumberOfdaysToBill(json_decode($this->activemodule), $fifteendaysago);
        $this->assertEquals(15,$days);

        $days = $test->getNumberOfdaysToBill(json_decode($this->activemodule), $thirtydaysago);
        $this->assertEquals(30,$days);

	// The max number of days is 30
        $days = $test->getNumberOfdaysToBill(json_decode($this->activemodule), $fortyfivedaysago);
        $this->assertEquals(30,$days);


	// modules marked to cancel will bill only up to cancel date
	$this->cancelmodule = json_decode($this->activemodule);
	$this->cancelmodule->pivot->cancelation_requested_at = $fourdaysago->format('Y-m-d H:i:s');
	$this->cancelmodule->pivot->cancel = 1;

        $days = $test->getNumberOfdaysToBill($this->cancelmodule, $thirtydaysago);
        $this->assertEquals(30-4, $days);



    }


    protected $activemodule = '{"id":2,"name":"Church\/Donor\/Marketing management","app_fee":"40.00","phone_number_fee":"0.00","sms_fee":"0.00","email_fee":"0.00","contact_fee":"0.03","created_at":null,"updated_at":"2020-11-30 00:01:10","deleted_at":null,"pivot":{"tenant_id":1,"module_id":2,"created_at":"2020-11-30 14:00:28","updated_at":"2020-11-30 00:01:10","app_fee":"0.00","phone_number_fee":"0.00","sms_fee":"0.00","email_fee":"0.00","contact_fee":"0.00","start_billing_at":null,"next_billing_at":null,"last_billing_at":null,"discount_amount":null,"promo_code":null,"cancel":0,"cancelation_requested_at":null,"deleted_at":null,"reactivate_on_paid_invoice_id":null}}';
}
