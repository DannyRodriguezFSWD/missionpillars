<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateDatatableStateTable extends Migration
{
    use App\Traits\CustomBlueprint;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('datatable_states', function (Blueprint $table) {
            $table->increments('id');
            
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->string('uri')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_user_search')->default(false);
            
            $table->unsignedBigInteger('time')->default(0);
            // $table->boolean('draw'); // don't think this is needed
            $table->longText('columns')->nullable();
            $table->longText('order')->nullable();
            $table->integer('start')->default(0);
            $table->integer('length')->default(10);
            $table->longText('search')->nullable();
            // $table->string('action')->nullable(); // don't think this is needed
            
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
        Schema::dropIfExists('datatable_states');
    }
}
