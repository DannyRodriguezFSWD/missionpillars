<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateTableEmailTracking extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_tracking', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->unsignedInteger('list_id')->nullable();
            $table->foreign('list_id')->references('id')->on('lists')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('email_id')->nullable();
            $table->foreign('email_id')->references('id')->on('emails')->onUpdate('cascade')->onDelete('cascade');
            $table->string('status', 255)->nullable();
            $table->longText('message')->nullable();
            
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
        Schema::dropIfExists('email_tracking');
    }
}
