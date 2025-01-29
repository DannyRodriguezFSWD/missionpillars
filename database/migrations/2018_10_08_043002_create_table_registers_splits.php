<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateTableRegistersSplits extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_splits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('register_id')->nullable();
            $table->foreign('register_id')->references('id')->on('registers')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('fund_id')->nullable();
            $table->foreign('fund_id')->references('id')->on('funds')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->double('amount', 10, 2)->nullable();
            $table->string('comment')->nullable();
            $table->string('tag')->nullable();

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
        Schema::dropIfExists('register_splits');
    }
}
