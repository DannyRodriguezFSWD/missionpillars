<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateModulePricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $chms = 2;
        DB::table('modules')->where('id',$chms)->update([
            'app_fee'=>40,
            'contact_fee'=>0.03,
            'phone_number_fee'=> 0,
            'sms_fee' => 0,
            'email_fee' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        $chms = 2;
        DB::table('modules')->where('id',$chms)->update([
            'app_fee'=>30,
            'contact_fee'=>0.03,
            'phone_number_fee'=> 0,
            'sms_fee' => 0,
            'email_fee' => 0
        ]);
    }
}
