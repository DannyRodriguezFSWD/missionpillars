<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class DeleteExternalApiRequestsTable extends Migration
{
    use CustomBlueprint;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('external_api_requests');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('external_api_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable()->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('mailgun_id')->nullable()->foreign('mailgun_id')->references('id')->on('mailgun')->onUpdate('cascade')->onDelete('cascade');
            $table->string('api', 255)->nullable();
            $table->timestamp('begin')->nullable();
            $table->timestamp('end')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $this->trackingFields($table);
        });
    }
}
