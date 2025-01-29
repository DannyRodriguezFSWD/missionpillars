<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class AlterContactEmailTableSetStatsFields extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_email', function (Blueprint $table) {
            $table->smallInteger('sent')->default(0);
            $table->smallInteger('status')->default(1); //1 = Not processed yet, 0 = Sent withouth errors, 2 = sent with errors
            $table->string('message', 255)->default('Not processed yet');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::table('contact_email', function (Blueprint $table) {
            //
        });
    }
}
