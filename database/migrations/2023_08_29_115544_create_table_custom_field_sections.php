<?php

use App\Traits\CustomBlueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomFieldSections extends Migration
{
    use CustomBlueprint;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_field_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->string('name')->nullable();
            $table->unsignedInteger('sort')->nullable();
            $table->timestamps();
            $this->trackingFields($table);
        });
        
        DB::statement("insert into custom_field_sections (name, created_at, updated_at) values ('Default', now(), now())");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_field_sections');
    }
}
