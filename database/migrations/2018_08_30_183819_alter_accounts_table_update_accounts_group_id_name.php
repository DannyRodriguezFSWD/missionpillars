<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class AlterAccountsTableUpdateAccountsGroupIdName extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('account_group_id')->nullable();
            $table->foreign('account_group_id')->references('id')->on('account_groups')->onUpdate('cascade')->onDelete('cascade');

            $table->string('name');
            $table->integer('number');
            $table->string('account_type')->nullable();
            $table->string('activity')->nullable();
            $table->boolean('status')->nullable();
            $table->boolean('sub_account')->nullable();
            $table->integer('parent_account_id')->nullable();
            $table->integer('order')->nullable();
            $table->unique(['tenant_id', 'number']);
            $table->unsignedInteger('account_fund_id')->nullable();
            $table->foreign('account_fund_id')->references('id')->on('funds')->onUpdate('cascade')->onDelete('cascade')->change();

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
