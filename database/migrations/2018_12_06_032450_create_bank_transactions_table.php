<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateBankTransactionsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('bank_institution_id')->nullable();
            $table->foreign('bank_institution_id')->references('id')->on('bank_institutions')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('bank_account_id')->nullable();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('transaction_id')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('amount')->nullable();
            $table->string('category_id')->nullable();
            $table->string('date')->nullable();
            $table->string('name')->nullable();
            $table->string('payee')->nullable();
            $table->string('payer')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_processor')->nullable();
            $table->string('ppd_id')->nullable();
            $table->string('reason')->nullable();
            $table->string('reference_number')->nullable();
            $table->boolean('pending');
            $table->boolean('mapped')->default(false);

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
        Schema::dropIfExists('bank_transactions');
    }
}
