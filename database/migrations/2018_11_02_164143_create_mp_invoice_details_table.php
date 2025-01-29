<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateMpInvoiceDetailsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_invoice_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);

            $table->unsignedInteger('mp_invoice_id')->nullable();
            $table->foreign('mp_invoice_id')->references('id')->on('mp_invoices')->onUpdate('cascade')->onDelete('cascade');
            $table->string('description', 255)->nullable();
            $table->decimal('amount', 12, 2)->default(0);

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
        Schema::dropIfExists('mp_invoice_details');
    }
}
