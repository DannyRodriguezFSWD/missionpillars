<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateSettingValuesTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('setting_id')->nullable();
            $table->foreign('setting_id')->references('id')->on('settings')->onUpdate('cascade')->onDelete('cascade');
            $table->string('key', 255)->nullable();
            $table->longText('value')->nullable();
            $table->string('input_type', 255)->nullable();//html inputs
            $table->longText('options')->nullable();
            $table->string('default', 255)->nullable();//default field value
            
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
        Schema::dropIfExists('setting_values');
    }
}
