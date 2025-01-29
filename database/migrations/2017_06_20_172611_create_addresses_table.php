<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateAddressesTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade')->onUpdate('cascade');
            
            $table->unsignedInteger('tenant_id');
            $this->setTenantIdForeignKey($table);
            
            $table->smallInteger('is_primary')->default(0);
            $table->string('mailing_address_1', 45)->nullable();
            $table->string('mailing_address_2', 45)->nullable();
            $table->string('p_o_box', 45)->nullable();
            $table->string('city', 45)->nullable();
            $table->string('region', 45)->nullable();
            $table->string('country', 45)->nullable();
            $table->string('postal_code', 45)->nullable();
            $table->string('type', 45)->nullable();
            $table->smallInteger('is_residence')->default(0);
            $table->smallInteger('is_mailing')->default(1);
            
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
        Schema::dropIfExists('addresses');
    }
}
