<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateChartsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('type', 255)->nullable();
            $table->string('measurement', 10)->default('#');
            $table->string('calculate', 10)->default('sum');
            $table->string('what', 10)->default('giving');
            $table->string('period', 50)->default('current_year');
            $table->timestamp('from')->nullable();
            $table->timestamp('to')->nullable();
            $table->string('group_by', 45)->default('months');
            $table->string('filter', 45)->default('none');
            $table->smallInteger('include_last_year')->default(0);
            
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
        Schema::dropIfExists('charts');
    }
}
