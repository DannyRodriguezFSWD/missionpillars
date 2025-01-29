<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreatePurchasedTicketsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchased_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('calendar_event_contact_register_id')->nullable();
            $table->foreign('calendar_event_contact_register_id')->references('id')->on('calendar_event_contact_register')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('amount')->default(0);
            $table->decimal('price', 8, 2)->default(0);
            $table->smallInteger('used')->default(0);
            $table->timestamp('used_at')->nullable();
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
        Schema::dropIfExists('purchased_tickets');
    }
}
