<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateCalendarEventsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->unsignedInteger('calendar_id')->nullable();
            $table->foreign('calendar_id')->references('id')->on('calendars')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('name', 255)->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('is_all_day')->default(0);
            
            $table->string('location', 255)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->text('check_in')->nullable();//stores json for whom goes this event
            
            $table->smallInteger('repeat')->default(0);
            $table->integer('repeat_every')->nullable();
            $table->enum('repeat_cycle', ['Never', 'Daily', 'Weekly', 'Monthly', 'Yearly'])->nullable();
            $table->enum('repeat_ends', ['Never', 'After', 'On'])->nullable();
            $table->integer('repeat_occurrences')->nullable();
            $table->timestamp('repeat_ends_on')->nullable();
            
            $this->trackingFields($table);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
}
