<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateTableContactRelatives extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_relatives', function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('relative_id')->nullable();
            $table->foreign('relative_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('relationship', 255)->nullable();
            
            $this->trackingFields($table);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_relatives');
    }
}
