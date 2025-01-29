<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateMpSoftwareInvoicesTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->text('reference')->nullable();
            $table->unsignedInteger('module_id')->nullable();
            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->string('module_name', 255)->nullable();
            $table->timestamp('billing_from')->nullable();
            $table->timestamp('billing_to')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->unsignedInteger('payment_option_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('mp_invoices');
    }
}
