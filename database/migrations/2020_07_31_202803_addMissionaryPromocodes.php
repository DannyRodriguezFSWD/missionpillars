<?php

use App\Models\Promocode;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissionaryPromocodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // reward column needs greater precision
        DB::statement("ALTER TABLE `promocodes`
            CHANGE COLUMN `reward` `reward` DOUBLE(10,4) NULL DEFAULT NULL AFTER `code`;");
        
        
        $crmcode = Promocode::firstOrNew([
            'code' => "missionarycrm2020", 
        ]);
        $crmcode->fill([
            'reward' => 0.625, 
            'quantity' => -1, 
            'expiry_date' => "2022-10-16 00:00:00" 
        ]);
        $crmcode->save();
        
        $acccode = Promocode::firstOrNew([
            'code' => "missionaryacc2020", 
        ]);
        $acccode->fill([
            'reward' => 0.4828, 
            'quantity' => -1, 
            'expiry_date' => "2022-10-16 00:00:00"
        ]);
        $acccode->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement("ALTER TABLE `promocodes`
            CHANGE COLUMN `reward` `reward` DOUBLE(10,2) NULL DEFAULT NULL AFTER `code`;");
        
    }
}
