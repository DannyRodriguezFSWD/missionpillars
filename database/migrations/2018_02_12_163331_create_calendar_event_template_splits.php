<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateCalendarEventTemplateSplits extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_template_splits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->unsignedInteger('calendar_event_template_id')->nullable();
            $table->foreign('calendar_event_template_id', 'fk_calendar_event_template_id')->references('id')->on('calendar_event_templates')->onUpdate('cascade')->onDelete('cascade');
            
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('uuid', 255)->nullable();
            
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
        Schema::table('calendar_event_template_splits', function (Blueprint $table) {
            $this->dropTrackingFields($table);
            $table->dropForeign(['tenant_id']);
            $table->dropForeign('fk_calendar_event_template_id');
        });
        Schema::dropIfExists('calendar_event_template_splits');
    }
}
