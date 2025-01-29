<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateChartOfAccountsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('parent_chart_of_account_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('page_type', 255)->nullable();
            $table->string('sub_type', 255)->nullable();
            $table->smallInteger('status')->nullable();
            $table->smallInteger('tax_deductable')->default(0);
            $table->enum('type', ['Internal', 'Missionary', 'External'])->nullable();
            
            $table->smallInteger('goal')->nullable();
            $table->enum('goal_cycle', ['once', 'monthly', 'yearly'])->nullable();
            
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
        Schema::dropIfExists('chart_of_accounts');
    }
}
