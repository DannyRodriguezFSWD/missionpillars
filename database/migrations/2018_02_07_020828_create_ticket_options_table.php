<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateTicketOptionsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_options', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('calendar_event_id');
            $table->foreign('calendar_event_id')->references('id')->on('calendar_events')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name', 255)->nullable();
            $table->decimal('price', 8, 2)->nullable();

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
        Schema::dropIfExists('ticket_options');
    }
}
