<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateFormTemplatesTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            /*
            $table->unsignedInteger('chart_if_account_id')->nullable();
            $table->foreign('chart_if_account_id')->references('id')->on('chart_if_accounts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('campaign_id')->nullable();
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onUpdate('cascade')->onDelete('cascade');
            */
            $table->string('name', 255)->nullable();
            $table->text('json')->nullable();
            $table->smallInteger('collect_funds')->default(0);
            $table->smallInteger('show_total')->default(0);
            $table->string('cover', 255)->nullable();
            $this->trackingFields($table);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_templates');
    }
}
