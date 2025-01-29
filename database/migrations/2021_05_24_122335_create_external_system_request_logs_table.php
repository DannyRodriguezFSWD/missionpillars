<?php

use App\Traits\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalSystemRequestLogsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_system_request_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category')->nullable();
            $table->string('performing_function')->nullable();
            $table->string('api_url')->nullable();
            $table->string('method')->nullable();
            $table->longText('request_data')->nullable();
            $table->text('request_headers')->nullable();
            $table->longText('response_data')->nullable();
            $table->integer('response_status_code')->nullable();
            $table->text('response_headers')->nullable();
            $table->text('full_backtrace')->nullable();
            $this->trackingFields($table);
            $table->timestamps();

            $table->index(['category','performing_function']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('external_system_request_logs');
    }
}
