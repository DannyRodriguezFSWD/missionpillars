<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateAccountsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('accounts_group_id')->nullable();
            $table->foreign('accounts_group_id')->references('id')->on('account_groups')->onUpdate('cascade')->onDelete('cascade');

            $table->string('name');
            $table->integer('number');
            $table->string('account_type');
            $table->string('activity');
            $table->boolean('status');
            $table->boolean('sub_account');
            $table->integer('parent_account_id');

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
        Schema::dropIfExists('accounts');
    }
}
