<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class AlterCalendarEventContactRegisterTable2 extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_contact_register', function (Blueprint $table) {
            $table->dropColumn(['form_filled', 'check_in', 'process_uuid']);
            $this->trackingFields($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_event_contact_register', function (Blueprint $table) {
            //
            $this->dropTrackingFields($table);
        });
    }
}
