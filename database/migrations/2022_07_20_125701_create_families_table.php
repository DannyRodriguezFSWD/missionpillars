<?php

use App\Traits\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFamiliesTable extends Migration
{
    use CustomBlueprint;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('families', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $this->setTenantIdForeignKey($table);
            $table->string('name', 255);
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('documents')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
            $this->trackingFields($table);
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
        Schema::dropIfExists('families');
    }
}
