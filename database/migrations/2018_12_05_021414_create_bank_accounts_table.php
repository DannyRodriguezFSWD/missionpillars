<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateBankAccountsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('bank_institution_id')->nullable();
            $table->foreign('bank_institution_id')->references('id')->on('bank_institutions')->onUpdate('cascade')->onDelete('cascade');
            $table->string('bank_account_id');
            $table->string('iso_currency_code')->nullable();
            $table->string('mask')->nullable();
            $table->string('name')->nullable();
            $table->string('official_name')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_subtype')->nullable();
            $table->string('current_balance')->nullable();
            $table->string('available_balance')->nullable();
            $table->string('limit_balance')->nullable();

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
        Schema::dropIfExists('bank_accounts');
    }
}
