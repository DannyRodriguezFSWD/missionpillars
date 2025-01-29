<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateTransactionsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('pledge_id')->nullable();
            $table->foreign('pledge_id')->references('id')->on('pledges')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('campaign_id')->nullable();
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onUpdate('cascade')->onDelete('cascade');
            
            $table->decimal('amount', 12, 2);
            $table->smallInteger('tax_deductible')->default(0);
            $table->string('payment_processors_transaction_id', 255)->nullable();
            $table->string('system_created_by', 255)->nullable();
            $table->string('status', 255)->nullable();
            
            $table->string('os', 255)->nullable();
            $table->string('browser', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('transaction_initiated_at')->nullable();
            $table->timestamp('transaction_last_updated_at')->nullable();
            $table->string('authorization_code', 255)->nullable();
            $table->string('failure_message', 300)->nullable();
            
            $table->enum('device_category', ['phone', 'tablet', 'laptop', 'desktop'])->nullable();
            $table->enum('transaction_path', ['text', 'kiosk', 'badge', 'facebook', 'continue to give', 'giversapp'])->nullable();
            $table->string('referrer', 255)->nullable();
            $table->string('type', 255)->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->string('anonymous_amount', 255)->nullable();
            $table->string('anonymous_identity', 255)->nullable();
            $table->integer('recurring_sequence')->default(0);
            
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
        Schema::dropIfExists('transactions');
    }
}
