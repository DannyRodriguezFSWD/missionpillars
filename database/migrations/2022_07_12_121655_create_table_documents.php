<?php

use App\Traits\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocuments extends Migration
{
    use CustomBlueprint;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $this->setTenantIdForeignKey($table);
            $table->nullableMorphs('relation');
            $table->string('name', 255);
            $table->string('disk', 255);
            $table->string('path', 1000)->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('mime_type', 255)->nullable();
            $table->boolean('is_temporary')->default(0);
            $table->string('uuid', 191);
            $table->timestamps();
            $this->trackingFields($table);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
