<?php

use App\Traits\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorLogsTable extends Migration
{
    use CustomBlueprint;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event')->nullable();
            $table->string('exception_type')->nullable();
            $table->string('caller_function')->nullable();
            $table->string('exception_code')->nullable();
            $table->text('error_message')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('line_number')->nullable();
            $table->string('requested_url')->nullable();
            $table->text('request_data')->nullable();
            $table->text('request_headers')->nullable();
            $table->text('extra')->nullable();
            $this->trackingFields($table);
            $table->timestamps();

            $table->index(['exception_type','caller_function','event']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('error_logs');
    }
}
