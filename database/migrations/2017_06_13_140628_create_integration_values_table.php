<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateIntegrationValuesTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $this->setTenantIdForeignKey($table);
            
            $table->string('key', 255);
            $table->text('value');
            
            $table->unsignedInteger('integration_id');
            $table->foreign('integration_id')->references('id')->on('integrations')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::dropIfExists('integration_values');
    }
}
