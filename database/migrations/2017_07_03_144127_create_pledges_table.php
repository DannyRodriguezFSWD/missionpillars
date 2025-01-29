<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreatePledgesTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pledges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->decimal('amount', 12, 2)->nullable();
            $table->integer('number_of_cycles')->nullable();
            $table->integer('remaining_cycles')->nullable();
            $table->enum('cycle_type', ['weekly', 'bi-weekly', 'monthly'])->nullable();
            $table->integer('total_successes')->nullable();
            $table->integer('total_failures')->nullable();
            $table->enum('status', ['ongoing', 'paused', 'terminated', 'completed'])->nullable();
            $table->string('system_created_by', 255)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
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
        Schema::dropIfExists('pledges');
    }
}
