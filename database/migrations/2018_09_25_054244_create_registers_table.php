<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateRegistersTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('account_register_id')->nullable();
            $table->foreign('account_register_id')->references('id')->on('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('fund_id')->nullable();
            $table->foreign('fund_id')->references('id')->on('funds')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('account_id')->nullable();
            $table->double('amount', 10, 2)->nullable();
            $table->string('comment')->nullable();
            $table->string('tag')->nullable();
            $table->string('date')->nullable();
            $table->string('check_number')->nullable();

            $this->trackingFields($table);
            $table->softDeletes();
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
        Schema::dropIfExists('registers');
    }
}
